'use client';

import Link from 'next/link';
import FavoriteButton from './FavoriteButton';

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

export default function BookCard({ book }: { book: Book }) {
  // Construct the full image URL
  const imageUrl = book.coverImage 
    ? (book.coverImage.startsWith('http') 
        ? book.coverImage 
        : `http://127.0.0.1:8000/uploads/covers/${book.coverImage}`)
    : null;

  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition relative">
      <FavoriteButton bookId={book.id} className="absolute top-2 right-2 z-10 shadow-lg" />
      
      <div className="h-64 bg-gray-200 flex items-center justify-center">
        {imageUrl ? (
          <img 
            src={imageUrl} 
            alt={book.title}
            className="h-full w-full object-cover"
          />
        ) : (
          <span className="text-gray-400 text-4xl">📚</span>
        )}
      </div>
      
      <div className="p-4">
        <h3 className="text-xl font-bold mb-2 line-clamp-2">{book.title}</h3>
        <p className="text-gray-600 mb-2">
          par {book.authors?.map(a => a.name).join(', ') || 'Auteur inconnu'}
        </p>
        <p className="text-sm text-gray-500 mb-2">
          {book.categories?.map(c => c.name).join(', ') || 'Catégorie inconnue'}
        </p>
        <p className="text-gray-700 text-sm line-clamp-3 mb-4">{book.description}</p>
        
        <div className="flex justify-between items-center mb-4">
          <span className="text-2xl font-bold text-primary-600">
            {parseFloat(book.price).toFixed(3)} DT
          </span>
          <div className="text-sm text-gray-500">
            <div>Stock: {book.stockQuantity}</div>
            <div>Available: {book.borrowableQuantity}</div>
          </div>
        </div>
        
        <Link 
          href={`/books/${book.id}`}
          className="block w-full bg-primary-600 text-white text-center py-2 rounded hover:bg-primary-700 transition"
        >
          View Details
        </Link>
      </div>
    </div>
  );
}
