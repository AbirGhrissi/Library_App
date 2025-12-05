'use client';

import { useState } from 'react';
import Link from 'next/link';
import { authAPI } from '@/lib/api';

export default function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [resetUrl, setResetUrl] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await authAPI.requestPasswordReset(email);
      setSuccess(true);
      // En développement, récupérer le lien direct
      if (response.data.resetUrl) {
        setResetUrl(response.data.resetUrl);
      }
    } catch (err: any) {
      setError('Une erreur est survenue. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
        <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
          <h2 className="text-3xl font-bold text-center mb-8">Lien de Réinitialisation</h2>
          
          {resetUrl ? (
            <>
              <div className="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                <p className="text-sm text-blue-800 mb-2">
                  <strong>Mode Développement:</strong> Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe.
                </p>
                <a 
                  href={resetUrl}
                  className="text-primary-600 hover:underline break-all text-sm"
                >
                  {resetUrl}
                </a>
              </div>
              <p className="text-xs text-gray-500 text-center mb-4">
                Note: En production, ce lien sera envoyé par email.
              </p>
            </>
          ) : (
            <p className="text-center text-gray-600 mb-4">
              Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.
            </p>
          )}
          
          <div className="text-center">
            <Link href="/login" className="text-primary-600 hover:underline">
              Retour à la Connexion
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
      <div className="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 className="text-3xl font-bold text-center mb-8">Mot de Passe Oublié</h2>
        
        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        <p className="text-gray-600 mb-6 text-center">
          Entrez votre adresse email et nous vous enverrons les instructions pour réinitialiser votre mot de passe.
        </p>

        <form onSubmit={handleSubmit}>
          <div className="mb-6">
            <label className="block text-gray-700 mb-2">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-primary-600 text-white py-2 rounded hover:bg-primary-700 transition disabled:bg-gray-400"
          >
            {loading ? 'Envoi...' : 'Envoyer les Instructions'}
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
