'use client';

import { useState, useEffect } from 'react';
import { booksAPI, categoriesAPI, authorsAPI, publishersAPI } from '@/lib/api';

interface SearchFilters {
  title: string;
  author: string;
  isbn: string;
  category: string;
  publisher: string;
  minPrice: string;
  maxPrice: string;
}

export default function BookSearch({ onSearch }: { onSearch: (books: any[]) => void }) {
  const [filters, setFilters] = useState<SearchFilters>({
    title: '',
    author: '',
    isbn: '',
    category: '',
    publisher: '',
    minPrice: '',
    maxPrice: '',
  });

  const [categories, setCategories] = useState<any[]>([]);
  const [authors, setAuthors] = useState<any[]>([]);
  const [publishers, setPublishers] = useState<any[]>([]);

  useEffect(() => {
    loadFilters();
  }, []);

  const loadFilters = async () => {
    try {
      const [catRes, authRes, pubRes] = await Promise.all([
        categoriesAPI.getAll(),
        authorsAPI.getAll(),
        publishersAPI.getAll(),
      ]);
      const catsData = catRes.data['hydra:member'] || catRes.data.member || catRes.data;
      const authsData = authRes.data['hydra:member'] || authRes.data.member || authRes.data;
      const pubsData = pubRes.data['hydra:member'] || pubRes.data.member || pubRes.data;
      
      setCategories(Array.isArray(catsData) ? catsData : []);
      setAuthors(Array.isArray(authsData) ? authsData : []);
      setPublishers(Array.isArray(pubsData) ? pubsData : []);
    } catch (error) {
      console.error('Erreur lors du chargement des filtres:', error);
      setCategories([]);
      setAuthors([]);
      setPublishers([]);
    }
  };

  const handleSearch = async () => {
    try {
      const params = Object.fromEntries(
        Object.entries(filters).filter(([_, value]) => value !== '')
      );
      const response = await booksAPI.search(params);
      onSearch(response.data);
    } catch (error) {
      console.error('Error searching books:', error);
    }
  };

  const handleReset = () => {
    setFilters({
      title: '',
      author: '',
      isbn: '',
      category: '',
      publisher: '',
      minPrice: '',
      maxPrice: '',
    });
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-md mb-8">
      <h2 className="text-2xl font-bold mb-4">Advanced Search</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <input
          type="text"
          placeholder="Title"
          value={filters.title}
          onChange={(e) => setFilters({ ...filters, title: e.target.value })}
          className="border rounded px-3 py-2"
        />
        
        <input
          type="text"
          placeholder="Author"
          value={filters.author}
          onChange={(e) => setFilters({ ...filters, author: e.target.value })}
          className="border rounded px-3 py-2"
        />
        
        <input
          type="text"
          placeholder="ISBN"
          value={filters.isbn}
          onChange={(e) => setFilters({ ...filters, isbn: e.target.value })}
          className="border rounded px-3 py-2"
        />
        
        <select
          value={filters.category}
          onChange={(e) => setFilters({ ...filters, category: e.target.value })}
          className="border rounded px-3 py-2"
        >
          <option value="">All Categories</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>{cat.name}</option>
          ))}
        </select>
        
        <select
          value={filters.publisher}
          onChange={(e) => setFilters({ ...filters, publisher: e.target.value })}
          className="border rounded px-3 py-2"
        >
          <option value="">All Publishers</option>
          {publishers.map((pub) => (
            <option key={pub.id} value={pub.id}>{pub.name}</option>
          ))}
        </select>
        
        <div className="flex gap-2">
          <input
            type="number"
            placeholder="Min Price"
            value={filters.minPrice}
            onChange={(e) => setFilters({ ...filters, minPrice: e.target.value })}
            className="border rounded px-3 py-2 w-1/2"
          />
          <input
            type="number"
            placeholder="Max Price"
            value={filters.maxPrice}
            onChange={(e) => setFilters({ ...filters, maxPrice: e.target.value })}
            className="border rounded px-3 py-2 w-1/2"
          />
        </div>
      </div>
      
      <div className="flex gap-4 mt-4">
        <button
          onClick={handleSearch}
          className="bg-primary-600 text-white px-6 py-2 rounded hover:bg-primary-700 transition"
        >
          Search
        </button>
        <button
          onClick={handleReset}
          className="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition"
        >
          Reset
        </button>
      </div>
    </div>
  );
}
