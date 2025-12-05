import Link from 'next/link'
import BookList from '@/components/books/BookList'

export default function Home() {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center mb-12">
        <h1 className="text-5xl font-bold mb-4 text-primary-600">
          Bienvenue à Notre Bibliothèque
        </h1>
        <p className="text-xl text-gray-600 mb-8">
          Découvrez, empruntez et achetez vos livres préférés
        </p>
        <div className="flex gap-4 justify-center">
          <Link 
            href="/books" 
            className="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition"
          >
            Parcourir les Livres
          </Link>
          <Link 
            href="/register" 
            className="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition"
          >
            Commencer
          </Link>
        </div>
      </div>
            
      <div className="mt-12">
        <h2 className="text-3xl font-bold mb-6">Livres en Vedette</h2>
        <BookList limit={6} />
      </div>
    </div>
  )
}
