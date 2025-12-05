import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import './globals.css'
import { AuthProvider } from '@/lib/AuthContext'
import { CartProvider } from '@/lib/CartContext'
import Navbar from '@/components/layout/Navbar'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'Système de Gestion de Bibliothèque',
  description: 'Parcourir, emprunter et acheter des livres en ligne',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="fr">
      <body className={inter.className}>
        <AuthProvider>
          <CartProvider>
            <Navbar />
            <main className="min-h-screen">
              {children}
            </main>
          </CartProvider>
        </AuthProvider>
      </body>
    </html>
  )
}
