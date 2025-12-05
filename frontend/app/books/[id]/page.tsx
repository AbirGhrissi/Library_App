'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { booksAPI } from '@/lib/api';
import { useAuth } from '@/lib/AuthContext';
import { useCart } from '@/lib/CartContext';

export default function BookDetail() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuth();
  const { addToCart } = useCart();
  const [book, setBook] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchBook();
  }, [params.id]);

  const fetchBook = async () => {
    try {
      const response = await booksAPI.getOne(Number(params.id));
      setBook(response.data);
    } catch (error) {
      console.error('Erreur lors du chargement du livre:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleBorrow = async () => {
    if (!user) {
      router.push('/login');
      return;
    }

    try {
      await booksAPI.borrow(book.id);
      setMessage('Livre emprunté avec succès!');
      fetchBook();
    } catch (error: any) {
      setMessage(error.response?.data?.error || 'Échec de l\'emprunt du livre');
    }
  };

  const handleAddToCart = async () => {
    if (!user) {
      router.push('/login');
      return;
    }

    try {
      await addToCart(book.id, quantity);
      setMessage('Livre ajouté au panier!');
    } catch (error: any) {
      setMessage(error.message || 'Échec de l\'ajout au panier');
    }
  };

  if (loading) {
    return <div className="container mx-auto px-4 py-8">Chargement...</div>;
  }

  if (!book) {
    return <div className="container mx-auto px-4 py-8">Livre non trouvé</div>;
  }

  // Construct the full image URL
  const imageUrl = book.coverImage 
    ? (book.coverImage.startsWith('http') 
        ? book.coverImage 
        : `http://127.0.0.1:8000/uploads/covers/${book.coverImage}`)
    : null;

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <div className="md:flex">
          <div className="md:w-1/3 bg-gray-200 flex items-center justify-center p-8">
            {imageUrl ? (
              <img 
                src={imageUrl} 
                alt={book.title}
                className="max-h-96 object-cover"
              />
            ) : (
              <span className="text-gray-400 text-8xl">📚</span>
            )}
          </div>
          
          <div className="md:w-2/3 p-8">
            <h1 className="text-4xl font-bold mb-4">{book.title}</h1>
            
            <div className="mb-6">
              <p className="text-xl text-gray-600 mb-2">
                par <span className="font-semibold">
                  {book.authors?.map(a => a.name).join(', ') || 'Auteur inconnu'}
                </span>
              </p>
              <p className="text-gray-600">
                Catégories: <span className="font-semibold">
                  {book.categories?.map(c => c.name).join(', ') || 'Catégorie inconnue'}
                </span>
              </p>
              {book.publisher && (
                <p className="text-gray-600">
                  Éditeur: <span className="font-semibold">{book.publisher.name}</span>
                </p>
              )}
              <p className="text-gray-600">ISBN: {book.isbn}</p>
              {book.publicationDate && (
                <p className="text-gray-600">
                  Publié: {new Date(book.publicationDate).toLocaleDateString('fr-FR')}
                </p>
              )}
            </div>
            
            <div className="mb-6">
              <h2 className="text-2xl font-bold mb-2">Description</h2>
              <p className="text-gray-700">{book.description}</p>
            </div>
            
            <div className="mb-6">
              <div className="flex items-center gap-8">
                <div>
                  <p className="text-3xl font-bold text-primary-600">
                    {parseFloat(book.price).toFixed(3)} DT
                  </p>
                </div>
                <div className="text-gray-600">
                  <p>Stock: {book.stockQuantity}</p>
                  <p>Disponible à emprunter: {book.borrowableQuantity}</p>
                </div>
              </div>
            </div>
            
            {message && (
              <div className={`mb-4 p-4 rounded ${
                message.includes('succès') 
                  ? 'bg-green-100 text-green-700' 
                  : 'bg-red-100 text-red-700'
              }`}>
                {message}
              </div>
            )}
            
            <div className="flex gap-4">
              <button
                onClick={handleBorrow}
                disabled={book.borrowableQuantity === 0}
                className="bg-primary-600 text-white px-6 py-3 rounded hover:bg-primary-700 transition disabled:bg-gray-400"
              >
                Emprunter
              </button>
              
              <div className="flex items-center gap-2">
                <input
                  type="number"
                  min="1"
                  max={book.stockQuantity}
                  value={quantity}
                  onChange={(e) => setQuantity(Number(e.target.value))}
                  className="border rounded px-3 py-2 w-20"
                />
                <button
                  onClick={handleAddToCart}
                  disabled={book.stockQuantity === 0}
                  className="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 transition disabled:bg-gray-400"
                >
                  Ajouter au Panier
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
