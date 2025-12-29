import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User } from '../types';
import api from '../services/api';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  loading: boolean;
  
  // Actions
  login: (username: string, password: string) => Promise<void>;
  register: (username: string, email: string, password: string, confirmPassword: string) => Promise<void>;
  logout: () => void;
  setUser: (user: User | null) => void;
  setToken: (token: string | null) => void;
  checkAuth: () => Promise<void>;
  clearAuth: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      loading: false,

      login: async (username: string, password: string) => {
        set({ loading: true });
        try {
          const response = await api.login({ username, password });
          
          if (response.success && response.data) {
            const { user, token } = response.data;
            
            set({
              user,
              token,
              isAuthenticated: true,
              loading: false
            });
            
            api.setToken(token);
            localStorage.setItem('axiofrp_user', JSON.stringify(user));
          } else {
            throw new Error(response.message || '登录失败');
          }
        } catch (error: any) {
          set({ loading: false });
          throw error;
        }
      },

      register: async (username: string, email: string, password: string, confirmPassword: string) => {
        set({ loading: true });
        try {
          const response = await api.register({ username, email, password, confirmPassword });
          
          if (response.success && response.data) {
            const { user, token } = response.data;
            
            set({
              user,
              token,
              isAuthenticated: true,
              loading: false
            });
            
            api.setToken(token);
            localStorage.setItem('axiofrp_user', JSON.stringify(user));
          } else {
            throw new Error(response.message || '注册失败');
          }
        } catch (error: any) {
          set({ loading: false });
          throw error;
        }
      },

      logout: () => {
        set({
          user: null,
          token: null,
          isAuthenticated: false
        });
        
        api.removeToken();
        localStorage.removeItem('axiofrp_user');
      },

      setUser: (user: User | null) => {
        set({ user });
      },

      setToken: (token: string | null) => {
        set({ token });
        if (token) {
          api.setToken(token);
        } else {
          api.removeToken();
        }
      },

      checkAuth: async () => {
        const token = api.getToken();
        if (!token) {
          set({ isAuthenticated: false });
          return;
        }

        try {
          const response = await api.getProfile();
          
          if (response.success && response.data) {
            const user = response.data;
            
            set({
              user,
              token,
              isAuthenticated: true
            });
            
            localStorage.setItem('axiofrp_user', JSON.stringify(user));
          } else {
            get().clearAuth();
          }
        } catch (error) {
          get().clearAuth();
        }
      },

      clearAuth: () => {
        set({
          user: null,
          token: null,
          isAuthenticated: false
        });
        
        api.removeToken();
        localStorage.removeItem('axiofrp_user');
      }
    }),
    {
      name: 'axiofrp-auth',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated
      })
    }
  )
);