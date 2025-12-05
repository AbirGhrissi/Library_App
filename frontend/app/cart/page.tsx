'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/lib/AuthContext';
import { useCart } from '@/lib/CartContext';
import Link from 'next/link';

export default function CartPage() {
  const { user, loading: authLoading } = useAuth();
  const { cart, loading, updateQuantity, removeFromCart, checkout } = useCart();
  const router = useRouter();
  const [message, setMessage] = useState('');

  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading]);

  const handleCheckout = async () => {
    try {
      await checkout();
      setMessage('Commande passée avec succès!');
      setTimeout(() => {
        router.push('/my-books');
      }, 2000);
    } catch (error: any) {
      setMessage(error.message || 'Erreur lors de la commande');
    }
  };

  const handleUpdateQuantity = async (itemId: number, newQuantity: number) => {
    try {
      await updateQuantity(itemId, newQuantity);
    } catch (error: any) {
      setMessage(error.message);
    }
  };

  const handleRemove = async (itemId: number) => {
    try {
      await removeFromCart(itemId);
      setMessage('Article retiré du panier');
    } catch (error: any) {
      setMessage(error.message);
    }
  };

  if (authLoading || loading) {
    return <div className="container mx-auto px-4 py-8">Chargement...</div>;
  }

  const imageUrl = (coverImage: string) => {
    return coverImage?.startsWith('http') 
      ? coverImage 
      : `http://127.0.0.1:8000/uploads/covers/${coverImage}`;
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Mon Panier</h1>

      {message && (
        <div className={`mb-4 p-4 rounded ${
          message.includes('succès') 
            ? 'bg-green-100 text-green-700' 
            : 'bg-red-100 text-red-700'
        }`}>
          {message}
        </div>
      )}

      {!cart || cart.items.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-xl text-gray-600 mb-4">Votre panier est vide</p>
          <Link 
            href="/books" 
            className="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition inline-block"
          >
            Parcourir les Livres
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow">
              {cart.items.map((item) => (
                <div key={item.id} className="flex gap-4 p-4 border-b last:border-b-0">
                  <div className="w-24 h-32 bg-gray-200 flex-shrink-0">
                    {item.book.coverImage ? (
                      <img 
                        src={imageUrl(item.book.coverImage)} 
                        alt={item.book.title}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-3xl">
                        📚
                      </div>
                    )}
                  </div>
                  
                  <div className="flex-1">
                    <h3 className="text-lg font-bold">{item.book.title}</h3>
                    <p className="text-gray-600 text-sm">
                      par {item.book.authors?.map(a => a.name).join(', ') || 'Auteur inconnu'}
                    </p>
                    <p className="text-primary-600 font-bold mt-2">
                      {parseFloat(item.book.price).toFixed(3)} DT
                    </p>
                    
                    <div className="flex items-center gap-4 mt-4">
                      <div className="flex items-center border rounded">
                        <button
                          onClick={() => handleUpdateQuantity(item.id, item.quantity - 1)}
                          className="px-3 py-1 hover:bg-gray-100"
                          disabled={loading}
                        >
                          -
                        </button>
                        <span className="px-4 py-1 border-x">{item.quantity}</span>
                        <button
                          onClick={() => handleUpdateQuantity(item.id, item.quantity + 1)}
                          className="px-3 py-1 hover:bg-gray-100"
                          disabled={loading}
                        >
                          +
                        </button>
                      </div>
                      
                      <button
                        onClick={() => handleRemove(item.id)}
                        className="text-red-600 hover:text-red-700"
                        disabled={loading}
                      >
                        Retirer
                      </button>
                    </div>
                  </div>
                  
                  <div className="text-right">
                    <p className="text-lg font-bold">{item.subtotal.toFixed(3)} DT</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
          
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow p-6 sticky top-4">
              <h2 className="text-2xl font-bold mb-4">Résumé</h2>
              
              <div className="space-y-2 mb-4">
                <div className="flex justify-between">
                  <span>Sous-total</span>
                  <span>{cart.total.toFixed(3)} DT</span>
                </div>
                <div className="flex justify-between text-sm text-gray-600">
                  <span>Nombre d'articles</span>
                  <span>{cart.items.reduce((sum, item) => sum + item.quantity, 0)}</span>
                </div>
              </div>
              
              <div className="border-t pt-4 mb-6">
                <div className="flex justify-between text-xl font-bold">
                  <span>Total</span>
                  <span>{cart.total.toFixed(3)} DT</span>
                </div>
              </div>
              
              <button
                onClick={handleCheckout}
                disabled={loading}
                className="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition disabled:bg-gray-400"
              >
                {loading ? 'Traitement...' : 'Passer la Commande'}
              </button>
              
              <Link 
                href="/books"
                className="block text-center text-primary-600 mt-4 hover:underline"
              >
                Continuer mes achats
              </Link>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
