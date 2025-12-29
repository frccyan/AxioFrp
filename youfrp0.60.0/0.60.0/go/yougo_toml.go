package main

import (
	"database/sql"
	"flag"
	"fmt"
	"io/ioutil"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
	_ "github.com/go-sql-driver/mysql"
)

// 全局变量
var userToken string
var selectedNodeID int // 添加选择的节点ID全局变量

// 配置信息
type Config struct {
	DBHost     string
	DBUser     string
	DBPassword string
	DBName     string
	FrpcPath   string
}

// 隧道信息
type Proxy struct {
	ID               int
	Username         string
	ProxyName        string
	ProxyType        string
	LocalIP          string
	LocalPort        int
	UseEncryption    string
	UseCompression   string
	Domain           string
	Locations        string
	HostHeaderRewrite string
	RemotePort       string
	Sk               string
	HeaderXFromWhere string
	Status           string
	LastUpdate       string
	Node             int
	CustomDomains    string
}

// 节点信息
type Node struct {
	ID       int
	Name     string
	Hostname string
	IP       string
	Port     int
	Token    string
}

// 用户信息
type User struct {
	ID       int
	Username string
	Token    string
	Status   string
}

func main() {
	// 解析命令行参数
	tokenFlag := flag.String("token", "", "访问密钥")
	nodeFlag := flag.String("node", "", "节点ID或IP") // 添加节点参数
	configPath := flag.String("config", "config.ini", "配置文件路径")
	mode := flag.String("mode", "both", "模式: generate(仅生成配置), run(仅运行frpc), both(生成配置并运行)")
	frpcPath := flag.String("frpc", "frpc.exe", "frpc可执行文件路径")
	overwrite := flag.Bool("overwrite", true, "是否覆盖已有的frpc.toml文件") // 默认覆盖
	autoRun := flag.Bool("auto-run", true, "是否自动运行frpc") // 默认自动运行
	flag.Parse()
	
	// 声明token变量
	var tokenInput string

	// 存储token的文件
	tokenFile := "token.txt"
	
	// 尝试从文件中读取token
	if *tokenFlag == "" {
		savedToken, err := ioutil.ReadFile(tokenFile)
		if err == nil && len(savedToken) > 0 {
			tokenInput = strings.TrimSpace(string(savedToken))
			fmt.Printf("已使用保存的访问密钥: %s\n", tokenInput)
		} else {
			// 如果文件不存在或为空，提示用户输入
			fmt.Print("请输入访问密钥: ")
			fmt.Scanln(&tokenInput) // 正确读取用户输入
			
			// 保存token到文件
			if tokenInput != "" {
				err = ioutil.WriteFile(tokenFile, []byte(tokenInput), 0644)
				if err != nil {
					fmt.Printf("保存访问密钥失败: %v\n", err)
				} else {
					fmt.Println("访问密钥已保存，下次将自动使用")
				}
			}
		}
	} else {
		tokenInput = *tokenFlag
		// 保存命令行提供的token
		err := ioutil.WriteFile(tokenFile, []byte(tokenInput), 0644)
		if err != nil {
			fmt.Printf("保存访问密钥失败: %v\n", err)
		}
	}

	// 设置全局用户token
	userToken = tokenInput

	// 加载配置
	config := loadConfig(*configPath)
	
	// 使用命令行参数覆盖frpc路径
	if *frpcPath != "frpc.exe" {
		config.FrpcPath = *frpcPath
	}

	// 使用自定义的连接方法
	db, err := sql.Open("mysql", fmt.Sprintf("%s:%s@tcp(%s)/%s?charset=utf8mb4&parseTime=True", 
		config.DBUser, config.DBPassword, config.DBHost, config.DBName))
	if err != nil {
		log.Fatalf("连接数据库失败: %v", err)
	}
	defer db.Close()

	// 验证token
	user, err := getUserByToken(db, tokenInput)
	if err != nil {
		log.Fatalf("验证访问密钥失败: %v", err)
	}

	// 处理节点选择
	nodeFile := "node.txt"
	
	// 1. 优先使用命令行参数中的节点
	if *nodeFlag != "" {
		fmt.Printf("使用命令行参数指定的节点: %s\n", *nodeFlag)
		selectedNodeID = parseNodeIdentifier(db, *nodeFlag)
		// 保存选择的节点到文件
		if selectedNodeID > 0 {
			saveNodeToFile(nodeFile, selectedNodeID)
		}
	} else {
		// 2. 尝试从node.txt文件读取节点ID
		nodeData, err := ioutil.ReadFile(nodeFile)
		if err == nil && len(nodeData) > 0 {
			nodeStr := strings.TrimSpace(string(nodeData))
			nodeID, err := strconv.Atoi(nodeStr)
			if err == nil && nodeID > 0 {
				// 验证节点是否存在
				node, err := getNodeByID(db, nodeID)
				if err == nil {
					selectedNodeID = nodeID
					fmt.Printf("使用保存的节点ID: %d (名称: %s, IP: %s)\n", selectedNodeID, node.Name, node.IP)
				}
			}
		}
		
		// 3. 如果没有指定节点或者指定的节点无效，则列出所有可用节点供用户选择
		if selectedNodeID <= 0 {
			selectedNodeID = selectNode(db)
			if selectedNodeID > 0 {
				saveNodeToFile(nodeFile, selectedNodeID)
			}
		}
	}

	// 如果没有选择节点，则退出
	if selectedNodeID <= 0 {
		log.Fatalf("未选择节点，程序退出")
	}

	// 获取用户的隧道列表
	proxies, err := getProxiesByUsername(db, user.Username)
	if err != nil {
		log.Fatalf("获取隧道列表失败: %v", err)
	}

	if len(proxies) == 0 {
		log.Println("没有找到可用的隧道")
		return
	}

	// 筛选出选中节点的隧道
	selectedProxies := []Proxy{}
	for _, proxy := range proxies {
		if proxy.Node == selectedNodeID {
			selectedProxies = append(selectedProxies, proxy)
		}
	}

	if len(selectedProxies) == 0 {
		log.Printf("选中的节点(ID=%d)没有可用的隧道，请重新选择节点\n", selectedNodeID)
		return
	}

	fmt.Printf("在节点(ID=%d)上找到 %d 个可用隧道\n", selectedNodeID, len(selectedProxies))
	
	// 获取选中的节点信息
	selectedNode, err := getNodeByID(db, selectedNodeID)
	if err != nil {
		log.Fatalf("获取节点信息失败: %v", err)
	}

	// 为选择的节点生成配置
	if *mode == "generate" || *mode == "both" {
		// 检查frpc.toml文件是否存在
		mainConfigExists := false
		if _, err := os.Stat("frpc.toml"); err == nil {
			mainConfigExists = true
		}
		
		// 如果主配置文件存在且不覆盖，直接跳过
		if mainConfigExists && !*overwrite {
			fmt.Println("检测到frpc.toml已存在，将使用现有配置文件")
			goto RunPart // 跳转到运行部分
		}
		
		// 否则直接覆盖
		
		fmt.Println("正在生成配置文件...")
		
		// 生成配置文件
		configFile := fmt.Sprintf("frpc_%d.toml", selectedNodeID)
		err = generateConfigTOML(configFile, selectedNode, selectedProxies)
		if err != nil {
			log.Fatalf("生成节点ID=%d的配置文件失败: %v", selectedNodeID, err)
		}
		
		fmt.Printf("已生成配置文件: %s\n", configFile)
		
		// 直接复制配置文件作为主配置文件
		configData, err := ioutil.ReadFile(configFile)
		if err != nil {
			log.Fatalf("读取配置文件失败: %v", err)
		}
		
		err = ioutil.WriteFile("frpc.toml", configData, 0644)
		if err != nil {
			log.Fatalf("创建主配置文件失败: %v", err)
		}
		fmt.Println("已生成主配置文件: frpc.toml")
	}
	
RunPart:

	// 启动frpc客户端
	if *mode == "run" || *mode == "both" {
		// 检查frpc.toml是否存在
		if _, err := os.Stat("frpc.toml"); err != nil {
			log.Fatalf("无法启动frpc客户端: frpc.toml文件不存在，请先生成配置文件")
		}
		
		fmt.Printf("正在使用 %s 启动frpc客户端...\n", config.FrpcPath)
		cmd := exec.Command(config.FrpcPath, "-c", "frpc.toml")
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
		err = cmd.Start()
		if err != nil {
			log.Fatalf("启动frpc客户端失败: %v", err)
		}
		
		fmt.Println("frpc客户端已启动")
		
		// 等待frpc运行
		fmt.Println("\nfrpc客户端正在运行，按回车键退出...")
		fmt.Scanln()
		
		// 结束进程
		if cmd.Process != nil {
			cmd.Process.Kill()
		}
	} else {
		// 如果只是生成配置，显示提示信息
		fmt.Println("\n配置文件已生成，可以使用以下命令运行frpc:")
		fmt.Printf("%s -c frpc.toml\n", config.FrpcPath)
		
		// 自动执行
		if *autoRun {
			fmt.Printf("\n正在自动执行: %s -c frpc.toml\n", config.FrpcPath)
			cmd := exec.Command(config.FrpcPath, "-c", "frpc.toml")
			cmd.Stdout = os.Stdout
			cmd.Stderr = os.Stderr
			err = cmd.Start()
			if err != nil {
				log.Fatalf("启动frpc客户端失败: %v", err)
			}
			
			fmt.Println("frpc客户端已启动")
			fmt.Println("\nfrpc客户端正在运行，按回车键退出...")
			fmt.Scanln()
			
			// 结束进程
			if cmd.Process != nil {
				cmd.Process.Kill()
			}
		} else {
			fmt.Println("\n按回车键退出...")
			fmt.Scanln()
		}
	}
}

// 解析节点标识符（可能是ID或IP）
func parseNodeIdentifier(db *sql.DB, nodeStr string) int {
	// 尝试将输入解析为数字（节点ID）
	nodeID, err := strconv.Atoi(nodeStr)
	if err == nil && nodeID > 0 {
		// 验证节点ID是否存在
		node, err := getNodeByID(db, nodeID)
		if err == nil {
			fmt.Printf("已选择节点ID: %d (名称: %s, IP: %s)\n", nodeID, node.Name, node.IP)
			return nodeID
		}
	}
	
	// 尝试通过IP查找节点
	nodes, err := getNodesByIP(db, nodeStr)
	if err != nil {
		fmt.Printf("通过IP查找节点失败: %v\n", err)
		return 0
	}
	
	if len(nodes) > 0 {
		fmt.Printf("通过IP(%s)找到节点: ID=%d, 名称=%s\n", nodeStr, nodes[0].ID, nodes[0].Name)
		return nodes[0].ID
	}
	
	fmt.Printf("未找到匹配的节点，请检查输入是否正确\n")
	return 0
}

// 保存节点ID到文件
func saveNodeToFile(filePath string, nodeID int) {
	nodeIDStr := strconv.Itoa(nodeID)
	err := ioutil.WriteFile(filePath, []byte(nodeIDStr), 0644)
	if err != nil {
		fmt.Printf("保存节点ID到文件失败: %v\n", err)
	} else {
		fmt.Printf("已将节点ID(%d)保存到文件，下次将自动使用\n", nodeID)
	}
}

// 让用户选择节点
func selectNode(db *sql.DB) int {
	// 获取所有可用的节点
	nodes, err := getAllNodes(db)
	if err != nil {
		fmt.Printf("获取节点列表失败: %v\n", err)
		return 0
	}
	
	if len(nodes) == 0 {
		fmt.Println("没有可用的节点")
		return 0
	}
	
	fmt.Println("\n===== 可用节点列表 =====")
	for i, node := range nodes {
		fmt.Printf("[%d] ID:%d 名称:%s IP:%s\n", i+1, node.ID, node.Name, node.IP)
	}
	
	var choice int
	fmt.Print("\n请选择节点编号 (1-", len(nodes), "): ")
	_, err = fmt.Scanln(&choice)
	if err != nil || choice < 1 || choice > len(nodes) {
		fmt.Println("无效的选择，请输入正确的节点编号")
		return selectNode(db) // 递归调用，重新选择
	}
	
	selectedNode := nodes[choice-1]
	fmt.Printf("已选择节点: %s (ID: %d, IP: %s)\n", selectedNode.Name, selectedNode.ID, selectedNode.IP)
	return selectedNode.ID
}

// 通过IP查找节点
func getNodesByIP(db *sql.DB, ip string) ([]Node, error) {
	nodes := []Node{}
	query := "SELECT id, name, hostname, ip, port, token FROM nodes WHERE ip = ? AND status = '200'"
	rows, err := db.Query(query, ip)
	if err != nil {
		return nodes, err
	}
	defer rows.Close()
	
	for rows.Next() {
		var node Node
		err := rows.Scan(&node.ID, &node.Name, &node.Hostname, &node.IP, &node.Port, &node.Token)
		if err != nil {
			fmt.Printf("扫描节点行失败: %v\n", err)
			continue
		}
		nodes = append(nodes, node)
	}
	
	return nodes, nil
}

// 获取所有可用节点
func getAllNodes(db *sql.DB) ([]Node, error) {
	nodes := []Node{}
	query := "SELECT id, name, hostname, ip, port, token FROM nodes WHERE status = '200'"
	rows, err := db.Query(query)
	if err != nil {
		return nodes, err
	}
	defer rows.Close()
	
	for rows.Next() {
		var node Node
		err := rows.Scan(&node.ID, &node.Name, &node.Hostname, &node.IP, &node.Port, &node.Token)
		if err != nil {
			fmt.Printf("扫描节点行失败: %v\n", err)
			continue
		}
		nodes = append(nodes, node)
	}
	
	return nodes, nil
}

// 加载配置文件
func loadConfig(configPath string) Config {
	// 默认配置
	config := Config{
		DBHost:     "23.247.131.63:3306",
		DBUser:     "frp",
		DBPassword: "tgx123456",
		DBName:     "frp",
		FrpcPath:   "frpc.exe", // 默认Windows
	}

	// 获取当前工作目录
	currentDir, err := os.Getwd()
	if err != nil {
		fmt.Println("警告：无法获取当前工作目录:", err)
	}

	// 自动检测frpc可执行文件并使用绝对路径
	frpcExePath := filepath.Join(currentDir, "frpc.exe")
	frpcPath := filepath.Join(currentDir, "frpc")
	
	if _, err := os.Stat(frpcExePath); err == nil {
		// Windows环境，frpc.exe存在
		config.FrpcPath = frpcExePath
		fmt.Println("检测到Windows环境，使用frpc.exe")
	} else if _, err := os.Stat(frpcPath); err == nil {
		// Linux/Mac环境，frpc存在
		config.FrpcPath = frpcPath
		fmt.Println("检测到Linux/Mac环境，使用frpc")
	} else {
		fmt.Println("警告：未检测到frpc或frpc.exe，使用默认值frpc.exe")
		config.FrpcPath = frpcExePath // 仍然使用完整路径
	}

	// TODO: 从配置文件加载配置
	// 简化实现，实际应该解析配置文件

	return config
}

// 根据token获取用户信息
func getUserByToken(db *sql.DB, token string) (User, error) {
	var user User
	query := "SELECT id, username, token, status FROM tokens WHERE token = ? AND status = '0'"
	err := db.QueryRow(query, token).Scan(&user.ID, &user.Username, &user.Token, &user.Status)
	if err != nil {
		return user, fmt.Errorf("访问密钥无效: %v", err)
	}
	return user, nil
}

// 根据用户名获取隧道列表
func getProxiesByUsername(db *sql.DB, username string) ([]Proxy, error) {
	proxies := []Proxy{}
	query := "SELECT id, username, proxy_name, proxy_type, local_ip, local_port, " + 
			"use_encryption, use_compression, domain, locations, host_header_rewrite, " + 
			"remote_port, sk, `header_X-From-Where`, status, lastupdate, node, customdomains " + 
			"FROM proxies WHERE username = ?"
	rows, err := db.Query(query, username)
	if err != nil {
		return proxies, err
	}
	defer rows.Close()

	for rows.Next() {
		var p Proxy
		err := rows.Scan(
			&p.ID, &p.Username, &p.ProxyName, &p.ProxyType, &p.LocalIP, &p.LocalPort,
			&p.UseEncryption, &p.UseCompression, &p.Domain, &p.Locations, &p.HostHeaderRewrite,
			&p.RemotePort, &p.Sk, &p.HeaderXFromWhere, &p.Status, &p.LastUpdate, &p.Node, &p.CustomDomains,
		)
		if err != nil {
			log.Printf("扫描隧道行失败: %v", err)
			continue
		}
		
		fmt.Printf("发现隧道: %s, 类型: %s, 加密: %s, 压缩: %s, 状态: %s\n", 
			p.ProxyName, p.ProxyType, p.UseEncryption, p.UseCompression, p.Status)
		proxies = append(proxies, p)
	}

	return proxies, nil
}

// 根据ID获取节点信息
func getNodeByID(db *sql.DB, id int) (Node, error) {
	var node Node
	query := "SELECT id, name, hostname, ip, port, token FROM nodes WHERE id = ? AND status = '200'"
	fmt.Printf("执行查询: %s, 参数: id=%d\n", query, id)
	
	// 先检查节点表中是否存在记录
	var count int
	countErr := db.QueryRow("SELECT COUNT(*) FROM nodes").Scan(&count)
	if countErr != nil {
		fmt.Printf("查询节点表总记录数失败: %v\n", countErr)
	} else {
		fmt.Printf("节点表中共有 %d 条记录\n", count)
	}
	
	// 检查指定ID的记录是否存在
	var idCount int
	countErr = db.QueryRow("SELECT COUNT(*) FROM nodes WHERE id = ?", id).Scan(&idCount)
	if countErr != nil {
		fmt.Printf("查询节点ID=%d记录数失败: %v\n", id, countErr)
	} else {
		fmt.Printf("节点表中ID=%d的记录有 %d 条\n", id, idCount)
	}
	
	err := db.QueryRow(query, id).Scan(&node.ID, &node.Name, &node.Hostname, &node.IP, &node.Port, &node.Token)
	if err != nil {
		return node, err
	}
	return node, nil
}

// 生成frpc TOML配置文件 (frp 0.60.0+格式)
func generateConfigTOML(configFile string, node Node, proxies []Proxy) error {
	var builder strings.Builder

	// 生成common配置 (TOML格式)
	builder.WriteString(fmt.Sprintf("serverAddr = \"%s\"\n", node.IP))
	builder.WriteString(fmt.Sprintf("serverPort = %d\n", node.Port))
	builder.WriteString("transport.tcpMux = true\n")
	builder.WriteString("transport.protocol = \"tcp\"\n")
	builder.WriteString(fmt.Sprintf("auth.method = \"token\"\n"))
	builder.WriteString(fmt.Sprintf("auth.token = \"%s\"\n", node.Token))
	builder.WriteString(fmt.Sprintf("user = \"%s\"\n", userToken)) // 使用token作为user
	builder.WriteString("dnsServer = \"114.114.114.114\"\n\n")

	// 生成每个代理的配置
	for _, proxy := range proxies {
		// 只处理状态为0（正常）的隧道
		if proxy.Status != "0" {
			continue
		}
		
		builder.WriteString("[[proxies]]\n")
		builder.WriteString(fmt.Sprintf("name = \"%s\"\n", proxy.ProxyName))
		builder.WriteString(fmt.Sprintf("type = \"%s\"\n", proxy.ProxyType))
		builder.WriteString(fmt.Sprintf("localIP = \"%s\"\n", proxy.LocalIP))
		builder.WriteString(fmt.Sprintf("localPort = %d\n", proxy.LocalPort))
		
		// 根据不同的隧道类型处理特殊字段
		switch strings.ToLower(proxy.ProxyType) {
		case "http", "https":
			// 处理HTTP/HTTPS特有的字段
			// 优先使用customdomains字段
			if proxy.CustomDomains != "" {
				builder.WriteString(fmt.Sprintf("customDomains = [\"%s\"]\n", proxy.CustomDomains))
			} else if proxy.Domain != "" && proxy.Domain != "[]" {
				// 处理域名字段，它可能是JSON格式的字符串数组
				if strings.HasPrefix(proxy.Domain, "[") && strings.HasSuffix(proxy.Domain, "]") {
					// 简单解析JSON数组
					domainStr := strings.TrimPrefix(strings.TrimSuffix(proxy.Domain, "]"), "[")
					domains := strings.Split(domainStr, ",")
					if len(domains) > 0 {
						// 清理掉多余的引号和空格
						domain := strings.Trim(strings.TrimSpace(domains[0]), "\"'")
						if domain != "" {
							builder.WriteString(fmt.Sprintf("customDomains = [\"%s\"]\n", domain))
						}
					}
				} else {
					builder.WriteString(fmt.Sprintf("customDomains = [\"%s\"]\n", proxy.Domain))
				}
			}
			
			// 如果有host_header_rewrite字段
			if proxy.HostHeaderRewrite != "" {
				builder.WriteString(fmt.Sprintf("hostHeaderRewrite = \"%s\"\n", proxy.HostHeaderRewrite))
			}
			
			// 如果有locations字段
			if proxy.Locations != "" {
				builder.WriteString(fmt.Sprintf("locations = \"%s\"\n", proxy.Locations))
			}
		case "stcp", "xtcp":
			// STCP/XTCP特有的配置
			if proxy.RemotePort != "" {
				builder.WriteString(fmt.Sprintf("remotePort = %s\n", proxy.RemotePort))
			}
			
			// sk是必须的参数
			if proxy.Sk != "" {
				builder.WriteString(fmt.Sprintf("transport.authKey = \"%s\"\n", proxy.Sk))
			} else {
				// 如果没有提供sk，使用默认值
				builder.WriteString("transport.authKey = \"tgx123456\"\n")
			}
		default:
			// TCP/UDP等其他类型的隧道
			if proxy.RemotePort != "" {
				builder.WriteString(fmt.Sprintf("remotePort = %s\n", proxy.RemotePort))
			}
		}
		
		// 根据数据库中的值设置加密和压缩
		// 解析使用加密的布尔值
		useEncryption := strings.ToLower(proxy.UseEncryption)
		fmt.Printf("隧道 %s 加密设置: %s\n", proxy.ProxyName, useEncryption)
		if useEncryption == "1" || useEncryption == "true" || useEncryption == "on" {
			builder.WriteString("transport.useEncryption = true\n")
		} else {
			builder.WriteString("transport.useEncryption = false\n")
		}
		
		// 解析使用压缩的布尔值
		useCompression := strings.ToLower(proxy.UseCompression)
		fmt.Printf("隧道 %s 压缩设置: %s\n", proxy.ProxyName, useCompression)
		if useCompression == "1" || useCompression == "true" || useCompression == "on" {
			builder.WriteString("transport.useCompression = true\n")
		} else {
			builder.WriteString("transport.useCompression = false\n")
		}
		builder.WriteString("\n")
	}

	// 写入配置文件
	return ioutil.WriteFile(configFile, []byte(builder.String()), 0644)
}