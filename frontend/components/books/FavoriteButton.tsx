'use client';

import { useState, useEffect } from 'react';
import { favoritesAPI } from '@/lib/api';
import { useAuth } from '@/lib/AuthContext';

interface FavoriteButtonProps {
  bookId: number;
  className?: string;
}

export default function FavoriteButton({ bookId, className = '' }: FavoriteButtonProps) {
  const [isFavorite, setIsFavorite] = useState(false);
  const [loading, setLoading] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    if (user) {
      checkFavorite();
    }
  }, [user, bookId]);

  const checkFavorite = async () => {
    try {
      const response = await favoritesAPI.check(bookId);
      setIsFavorite(response.data.isFavorite);
    } catch (error) {
      console.error('Erreur lors de la vérification:', error);
    }
  };

  const toggleFavorite = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();

    if (!user) {
      alert('Veuillez vous connecter pour ajouter des favoris');
      return;
    }

    setLoading(true);
    try {
      if (isFavorite) {
        await favoritesAPI.remove(bookId);
        setIsFavorite(false);
      } else {
        await favoritesAPI.add(bookId);
        setIsFavorite(true);
      }
    } catch (error: any) {
      alert(error.response?.data?.error || 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return null;
  }

  return (
    <button
      onClick={toggleFavorite}
      disabled={loading}
      className={`p-2 rounded-full transition-all ${
        isFavorite
          ? 'bg-red-500 text-white hover:bg-red-600'
          : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
      } ${loading ? 'opacity-50 cursor-not-allowed' : ''} ${className}`}
      title={isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        className="h-5 w-5"
        viewBox="0 0 20 20"
        fill={isFavorite ? 'currentColor' : 'none'}
        stroke="currentColor"
        strokeWidth={isFavorite ? 0 : 2}
      >
        <path
          fillRule="evenodd"
          d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
          clipRule="evenodd"
        />
      </svg>
    </button>
  );
}
