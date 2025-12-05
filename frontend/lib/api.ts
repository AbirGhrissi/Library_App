import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle token expiration
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;

// Auth APIs
export const authAPI = {
  register: (data: any) => api.post('/register', data),
  login: (email: string, password: string) => api.post('/login', { username: email, password }),
  me: () => api.get('/me'),
  requestPasswordReset: (email: string) => api.post('/password/reset/request', { email }),
  resetPassword: (token: string, password: string) => api.post('/password/reset/confirm', { token, password }),
};

// Books APIs
export const booksAPI = {
  getAll: (params?: any) => api.get('/books', { params }),
  getOne: (id: number) => api.get(`/books/${id}`),
  search: (params: any) => api.get('/books/search', { params }),
  borrow: (bookId: number) => api.post('/borrowings', { bookId }),
  purchase: (bookId: number, quantity: number) => api.post('/purchases', { 
    book: `/api/books/${bookId}`,
    quantity 
  }),
};

// User APIs
export const userAPI = {
  getBorrowings: () => api.get('/user/borrowings'),
  getPurchases: () => api.get('/user/purchases'),
  updateProfile: (id: number, data: any) => api.put(`/users/${id}`, data),
};

// Categories APIs
export const categoriesAPI = {
  getAll: () => api.get('/categories'),
};

// Authors APIs
export const authorsAPI = {
  getAll: () => api.get('/authors'),
};

// Publishers APIs
export const publishersAPI = {
  getAll: () => api.get('/publishers'),
};

// Cart APIs
export const cartAPI = {
  getCart: () => api.get('/cart'),
  addItem: (bookId: number, quantity: number = 1) => 
    api.post('/cart/items', { bookId, quantity }),
  updateItem: (itemId: number, quantity: number) => 
    api.put(`/cart/items/${itemId}`, { quantity }),
  removeItem: (itemId: number) => api.delete(`/cart/items/${itemId}`),
  clear: () => api.delete('/cart/clear'),
  checkout: () => api.post('/cart/checkout'),
};

// Favorites APIs
export const favoritesAPI = {
  getAll: () => api.get('/favorites'),
  add: (bookId: number) => api.post(`/favorites/${bookId}`),
  remove: (bookId: number) => api.delete(`/favorites/${bookId}`),
  check: (bookId: number) => api.get(`/favorites/check/${bookId}`),
};
