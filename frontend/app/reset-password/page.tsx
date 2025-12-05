'use client';

import { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { authAPI } from '@/lib/api';

function ResetPasswordForm() {
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get('token');

  useEffect(() => {
    if (!token) {
      setError('Invalid or missing reset token');
    }
  }, [token]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (password !== confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    if (!token) {
      setError('Invalid reset token');
      return;
    }

    setLoading(true);

    try {
      await authAPI.resetPassword(token, password);
      setSuccess(true);
      setTimeout(() => {
        router.push('/login');
      }, 3000);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to reset password');
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
        <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
          <h2 className="text-3xl font-bold text-center mb-8 text-green-600">Succès!</h2>
          <p className="text-center text-gray-600 mb-4">
            Votre mot de passe a été réinitialisé avec succès. Redirection vers la connexion...
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
      <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 className="text-3xl font-bold text-center mb-8">Réinitialiser le Mot de Passe</h2>
        
        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="block text-gray-700 mb-2">Nouveau Mot de Passe</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={6}
              className="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div className="mb-6">
            <label className="block text-gray-700 mb-2">Confirmer le Mot de Passe</label>
            <input
              type="password"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              required
              minLength={6}
              className="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading || !token}
            className="w-full bg-primary-600 text-white py-2 rounded hover:bg-primary-700 transition disabled:bg-gray-400"
          >
            {loading ? 'Réinitialisation...' : 'Réinitialiser le Mot de Passe'}
          </button>
        </form>

        <div className="mt-4 text-center">
          <Link href="/login" className="text-primary-600 hover:underline">
            Retour à la Connexion
          </Link>
        </div>
      </div>
    </div>
  );
}

export default function ResetPassword() {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
        <div className="text-center">Loading...</div>
      </div>
    }>
      <ResetPasswordForm />
    </Suspense>
  );
}
