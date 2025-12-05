'use client';

import { useState } from 'react';
import BookSearch from '@/components/books/BookSearch';
import BookCard from '@/components/books/BookCard';

export default function BooksPage() {
  const [searchResults, setSearchResults] = useState<any[]>([]);

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Browse Books</h1>
      
      <BookSearch onSearch={setSearchResults} />
      
      {searchResults.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {searchResults.map((book) => (
            <BookCard key={book.id} book={book} />
          ))}
        </div>
      ) : (
        <div className="text-center text-gray-600 py-8">
          Use the search filters above to find books
        </div>
      )}
    </div>
  );
}
