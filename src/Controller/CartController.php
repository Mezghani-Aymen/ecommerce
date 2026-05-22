<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        
        $cartWithData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $cartWithData[] = [
                    'produit' => $produit,
                    'quantity' => $quantity
                ];
                $total += $produit->getPrix() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    /**
     * @Route("/add/{id}", name="cart_add")
     */
    public function add($id): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Le jeu a été ajouté à votre panier.');

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/update/{id}", name="cart_update", methods={"POST"})
     */
    public function update($id, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity > 0) {
            $cart[$id] = $quantity;
        } else {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/remove/{id}", name="cart_remove")
     */
    public function remove($id): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
}
