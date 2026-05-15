<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="admin_product_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/add", name="admin_product_add", methods={"POST"})
     */
    public function add(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit); // Create form based on the ProduitType
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            $this->addFlash('success', 'Jeu ajouté avec succès !');

            return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('product/add.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            $this->addFlash('success', 'Jeu modifié avec succès !');

            return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="admin_product_delete", methods={"POST"})
     */
    public function delete(Request $request, int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            try {
                $produitRepository->remove($produit, true);
                $this->addFlash('success', 'Jeu supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Impossible de supprimer ce jeu car il est lié à une commande existante.');
            }
        }

        return $this->redirectToRoute('admin_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
