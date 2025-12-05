'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import api from './api';
import { useAuth } from './AuthContext';

interface CartItem {
  id: number;
  book: {
    id: number;
    title: string;
    price: string;
    coverImage: string;
    author: { name: string };
  };
  quantity: number;
  subtotal: number;
}

interface Cart {
  id: number;
  items: CartItem[];
  total: number;
}

interface CartContextType {
  cart: Cart | null;
  loading: boolean;
  addToCart: (bookId: number, quantity: number) => Promise<void>;
  updateQuantity: (itemId: number, quantity: number) => Promise<void>;
  removeFromCart: (itemId: number) => Promise<void>;
  checkout: () => Promise<void>;
  refreshCart: () => Promise<void>;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

export function CartProvider({ children }: { children: ReactNode }) {
  const [cart, setCart] = useState<Cart | null>(null);
  const [loading, setLoading] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    if (user) {
      refreshCart();
    } else {
      setCart(null);
    }
  }, [user]);

  const refreshCart = async () => {
    if (!user) return;
    
    try {
      const response = await api.get('/cart');
      setCart(response.data);
    } catch (error) {
      console.error('Erreur lors du chargement du panier:', error);
    }
  };

  const addToCart = async (bookId: number, quantity: number = 1) => {
    setLoading(true);
    try {
      await api.post('/cart/add', { bookId, quantity });
      await refreshCart();
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Erreur lors de l\'ajout au panier');
    } finally {
      setLoading(false);
    }
  };

  const updateQuantity = async (itemId: number, quantity: number) => {
    setLoading(true);
    try {
      await api.put(`/cart/update/${itemId}`, { quantity });
      await refreshCart();
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Erreur lors de la mise à jour');
    } finally {
      setLoading(false);
    }
  };

  const removeFromCart = async (itemId: number) => {
    setLoading(true);
    try {
      await api.delete(`/cart/remove/${itemId}`);
      await refreshCart();
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Erreur lors de la suppression');
    } finally {
      setLoading(false);
    }
  };

  const checkout = async () => {
    setLoading(true);
    try {
      await api.post('/cart/checkout');
      await refreshCart();
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Erreur lors de la commande');
    } finally {
      setLoading(false);
    }
  };

  return (
    <CartContext.Provider value={{ cart, loading, addToCart, updateQuantity, removeFromCart, checkout, refreshCart }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart doit être utilisé dans un CartProvider');
  }
  return context;
}
