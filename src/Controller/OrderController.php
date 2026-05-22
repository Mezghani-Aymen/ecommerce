<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/checkout", name="order_checkout")
     */
    public function checkout(RequestStack $requestStack, ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide, vous ne pouvez pas passer commande.');
            return $this->redirectToRoute('cart_index');
        }

        $commande = new Commande();
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $item = new CommandeItem();
                $item->setProduit($produit);
                $item->setQuantite($quantity);
                $commande->addItem($item);

                $total += $produit->getPrix() * $quantity;
            }
        }

        $commande->setTotal($total);

        // Link to user
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour passer commande.');
            return $this->redirectToRoute('app_login');
        }
        $commande->setUser($user);

        // Save commande
        $commandeRepository->save($commande, true);

        // Clear cart
        $session->remove('cart');

        return $this->render('order/success.html.twig', [
            'commande' => $commande
        ]);
    }
}
