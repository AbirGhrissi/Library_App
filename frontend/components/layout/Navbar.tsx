'use client';

import Link from 'next/link';
import { useAuth } from '@/lib/AuthContext';
import { useCart } from '@/lib/CartContext';

export default function Navbar() {
  const { user, logout } = useAuth();
  const { cart } = useCart();

  const cartItemCount = cart?.items.reduce((sum, item) => sum + item.quantity, 0) || 0;

  return (
    <nav className="bg-white shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-16">
          <Link href="/" className="text-2xl font-bold text-primary-600">
            Bibliothèque
          </Link>

          <div className="flex items-center space-x-6">
            <Link href="/books" className="text-gray-700 hover:text-primary-600 transition">
              Livres
            </Link>
            
            {user ? (
              <>
                <Link href="/my-books" className="text-gray-700 hover:text-primary-600 transition">
                  Mes Livres
                </Link>
                <Link href="/favorites" className="text-gray-700 hover:text-primary-600 transition">
                 Favoris
                </Link>
                <Link href="/cart" className="relative text-gray-700 hover:text-primary-600 transition">
                   Panier
                  {cartItemCount > 0 && (
                    <span className="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                      {cartItemCount}
                    </span>
                  )}
                </Link>
                <button
                  onClick={logout}
                  className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
                >
                  Déconnexion
                </button>
              </>
            ) : (
              <>
                <Link 
                  href="/login" 
                  className="text-gray-700 hover:text-primary-600 transition"
                >
                  Connexion
                </Link>
                <Link 
                  href="/register" 
                  className="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700 transition"
                >
                  Inscription
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}
