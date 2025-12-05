<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/password', name: 'api_password_')]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {
    }

    #[Route('/reset/request', name: 'reset_request', methods: ['POST'])]
    public function requestReset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email is required'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Always return success to prevent email enumeration
        if (!$user) {
            return $this->json(['message' => 'If the email exists, a reset link has been sent'], 200);
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->flush();

        // Generate reset URL
        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3001';
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token;

        // En développement: retourner le lien directement
        // En production: envoyer par email
        if ($_ENV['APP_ENV'] === 'dev') {
            return $this->json([
                'message' => 'Lien de réinitialisation généré',
                'resetUrl' => $resetUrl,
                'token' => $token,
                'note' => 'En développement: utilisez ce lien directement. En production, il sera envoyé par email.'
            ], 200);
        }

        // Send email (en production)
        try {
            $email = (new TemplatedEmail())
                ->from($_ENV['MAILER_FROM'] ?? 'noreply@library.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de mot de passe - Library App')
                ->htmlTemplate('emails/password_reset.html.twig')
                ->context([
                    'resetUrl' => $resetUrl,
                    'user' => $user
                ]);

            $this->mailer->send($email);
            return $this->json(['message' => 'Un email a été envoyé avec les instructions'], 200);
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            error_log('Erreur envoi email de réinitialisation: ' . $e->getMessage());
            
            // En cas d'erreur d'envoi, retourner le lien quand même en dev
            if ($_ENV['APP_ENV'] === 'dev') {
                return $this->json([
                    'message' => 'Erreur d\'envoi d\'email (mode dev)',
                    'resetUrl' => $resetUrl,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // En production, ne pas révéler les détails de l'erreur
            return $this->json([
                'message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    #[Route('/reset/confirm', name: 'reset_confirm', methods: ['POST'])]
    public function confirmReset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['error' => 'Token and password are required'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || !$user->getResetTokenExpiresAt() || $user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            return $this->json(['error' => 'Invalid or expired token'], 400);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->entityManager->flush();

        return $this->json(['message' => 'Password reset successfully'], 200);
    }
}
