'use client';

import { useEffect, useState } from 'react';
import { booksAPI } from '@/lib/api';
import BookCard from './BookCard';

interface Book {
  id: number;
  title: string;
  isbn: string;
  description: string;
  price: string;
  coverImage: string;
  stockQuantity: number;
  borrowableQuantity: number;
  author: { name: string };
  category: { name: string };
  publisher?: { name: string };
}

export default function BookList({ limit }: { limit?: number }) {
  const [books, setBooks] = useState<Book[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchBooks();
  }, []);

  const fetchBooks = async () => {
    try {
      const params = limit ? { itemsPerPage: limit } : {};
      const response = await booksAPI.getAll(params);
      // API Platform returns data in 'member' field
      const booksData = response.data['hydra:member'] || response.data.member || response.data;
      setBooks(Array.isArray(booksData) ? booksData : []);
    } catch (error) {
      console.error('Erreur lors du chargement des livres:', error);
      setBooks([]);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="text-center py-8">Loading books...</div>;
  }

  if (books.length === 0) {
    return <div className="text-center py-8">No books available</div>;
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {books.map((book) => (
        <BookCard key={book.id} book={book} />
      ))}
    </div>
  );
}
