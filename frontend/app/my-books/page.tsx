'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { userAPI } from '@/lib/api';
import { useAuth } from '@/lib/AuthContext';

export default function MyBooks() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [borrowings, setBorrowings] = useState<any[]>([]);
  const [purchases, setPurchases] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    } else if (user) {
      fetchMyBooks();
    } 
  }, [user, authLoading]);

  const fetchMyBooks = async () => {
    try {
      const [borrowingsRes, purchasesRes] = await Promise.all([
        userAPI.getBorrowings(),
        userAPI.getPurchases(),
      ]);
      const borrowingsData = borrowingsRes.data['hydra:member'] || borrowingsRes.data.member || borrowingsRes.data;
      const purchasesData = purchasesRes.data['hydra:member'] || purchasesRes.data.member || purchasesRes.data;
      
      setBorrowings(Array.isArray(borrowingsData) ? borrowingsData : []);
      setPurchases(Array.isArray(purchasesData) ? purchasesData : []);
    } catch (error) {
      console.error('Erreur lors du chargement des livres:', error);
      setBorrowings([]);
      setPurchases([]);
    } finally {
      setLoading(false);
    }
  };

  if (authLoading || loading) {
    return <div className="container mx-auto px-4 py-8">Chargement...</div>;
  }

  const getStatusLabel = (status: string) => {
    const labels: { [key: string]: string } = {
      'active': 'Actif',
      'pending_return': 'En attente de validation',
      'returned': 'Retourné',
      'overdue': 'En retard',
      'completed': 'Complété',
      'pending': 'En attente',
      'cancelled': 'Annulé'
    };
    return labels[status] || status;
  };

  const handleRequestReturn = async (borrowingId: number) => {
    if (!confirm('Voulez-vous vraiment demander le retour de ce livre ?')) {
      return;
    }

    try {
      const response = await fetch(`http://localhost:8000/api/borrowings/${borrowingId}/request-return`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });

      if (response.ok) {
        alert('Demande de retour enregistrée ! En attente de validation par l\'admin.');
        fetchMyBooks(); // Recharger les données
      } else {
        const error = await response.json();
        alert(error.error || 'Erreur lors de la demande de retour');
      }
    } catch (error) {
      console.error('Erreur:', error);
      alert('Erreur lors de la demande de retour');
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Mes Livres</h1>

      <div className="mb-12">
        <h2 className="text-2xl font-bold mb-4">Livres Empruntés</h2>
        {borrowings.length === 0 ? (
          <p className="text-gray-600">Vous n'avez pas encore emprunté de livres.</p>
        ) : (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livre</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emprunté le</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">À retourner le</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {borrowings.map((borrowing) => (
                  <tr key={borrowing.id}>
                    <td className="px-6 py-4">{borrowing.book.title}</td>
                    <td className="px-6 py-4">
                      {new Date(borrowing.borrowedAt).toLocaleDateString('fr-FR')}
                    </td>
                    <td className="px-6 py-4">
                      {new Date(borrowing.dueDate).toLocaleDateString('fr-FR')}
                    </td>
                    <td className="px-6 py-4">
                      <span className={`px-2 py-1 rounded text-sm ${
                        borrowing.status === 'active' 
                          ? 'bg-green-100 text-green-800'
                          : borrowing.status === 'pending_return'
                          ? 'bg-yellow-100 text-yellow-800'
                          : borrowing.status === 'returned'
                          ? 'bg-gray-100 text-gray-800'
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {getStatusLabel(borrowing.status)}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      {borrowing.status === 'active' && (
                        <button
                          onClick={() => handleRequestReturn(borrowing.id)}
                          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm"
                        >
                          Demander le retour
                        </button>
                      )}
                      {borrowing.status === 'pending_return' && (
                        <span className="text-sm text-yellow-600">
                          En attente de validation
                        </span>
                      )}
                      {borrowing.status === 'returned' && (
                        <span className="text-sm text-green-600">
                          ✓ Retourné
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <div>
        <h2 className="text-2xl font-bold mb-4">Livres Achetés</h2>
        {purchases.length === 0 ? (
          <p className="text-gray-600">Vous n'avez pas encore acheté de livres.</p>
        ) : (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livre</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {purchases.map((purchase) => (
                  <tr key={purchase.id}>
                    <td className="px-6 py-4">{purchase.book.title}</td>
                    <td className="px-6 py-4">
                      {new Date(purchase.purchasedAt).toLocaleDateString('fr-FR')}
                    </td>
                    <td className="px-6 py-4">{purchase.quantity}</td>
                    <td className="px-6 py-4">{purchase.price} €</td>
                    <td className="px-6 py-4">
                      <span className={`px-2 py-1 rounded text-sm ${
                        purchase.status === 'completed' 
                          ? 'bg-green-100 text-green-800'
                          : purchase.status === 'pending'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-red-100 text-red-800'
                      }`}>
                        {getStatusLabel(purchase.status)}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
