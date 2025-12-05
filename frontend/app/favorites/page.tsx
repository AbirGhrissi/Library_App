'use client';

import { useEffect, useState } from 'react';
import { favoritesAPI } from '@/lib/api';
import BookCard from '@/components/books/BookCard';
import { useAuth } from '@/lib/AuthContext';
import { useRouter } from 'next/navigation';

interface Book {
  id: number;
  title: string;
  isbn: string;
  price: number;
  coverImage: string | null;
  author: { id: number; name: string } | null;
  publisher: { id: number; name: string } | null;
  categories: Array<{ id: number; name: string }>;
}

export default function FavoritesPage() {
  const [favorites, setFavorites] = useState<Book[]>([]);
  const [loading, setLoading] = useState(true);
  const { user } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!user) {
      router.push('/login');
      return;
    }

    loadFavorites();
  }, [user, router]);

  const loadFavorites = async () => {
    try {
      const response = await favoritesAPI.getAll();
      setFavorites(response.data.favorites);
    } catch (error) {
      console.error('Erreur lors du chargement des favoris:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleRemoveFavorite = async (bookId: number) => {
    try {
      await favoritesAPI.remove(bookId);
      setFavorites(favorites.filter(book => book.id !== bookId));
    } catch (error) {
      console.error('Erreur lors de la suppression:', error);
    }
  };

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold">Mes Livres Favoris</h1>
        <span className="text-gray-600">{favorites.length} livre(s)</span>
      </div>

      {favorites.length === 0 ? (
        <div className="text-center py-16">
          <div className="text-6xl mb-4">📚</div>
          <h2 className="text-2xl font-semibold mb-2">Aucun favori pour le moment</h2>
          <p className="text-gray-600 mb-6">
            Parcourez notre catalogue et ajoutez vos livres préférés !
          </p>
          <button
            onClick={() => router.push('/books')}
            className="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600"
          >
            Découvrir les livres
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {favorites.map((book) => (
            <div key={book.id} className="relative">
              <BookCard book={book} />
              <button
                onClick={() => handleRemoveFavorite(book.id)}
                className="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow-lg z-10"
                title="Retirer des favoris"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fillRule="evenodd"
                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                    clipRule="evenodd"
                  />
                </svg>
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
