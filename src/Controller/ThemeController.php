<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Service\ThemeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ThemeController extends AbstractController
{
    public function __construct(private readonly ThemeManager           $themeManager,
                                private readonly EntityManagerInterface $em,
                                private readonly SerializerInterface    $serializer)
    {
    }

    /**
     * @Route("/api/themes", methods={"POST"})
     */
    public function createTheme(Request $request): JsonResponse
    {

        $data = $request->getContent();
        $theme = $this->serializer->deserialize($data, Theme::class, 'json');
        $this->themeManager->handleIsDefault($theme);

        if (!$theme->getName() || !$theme->getColors()) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }


        $this->em->persist($theme);
        $this->em->flush();

        return new JsonResponse(['message' => 'Theme created successfully'], 201);
    }


    /**
     * @Route("/api/themes/{id}", methods={"PUT"})
     */
    public function updateTheme(Request $request, int $id): JsonResponse
    {
        $data = $request->getContent();
        $theme = $this->serializer->deserialize($data, Theme::class, 'json');

        $existingTheme = $this->em->getRepository(Theme::class)->find($id);

        if (!$existingTheme) {
            return new JsonResponse(['error' => 'Theme not found'], 404);
        }


        if ($existingTheme->getIsDefault() && !$theme->getIsDefault()) {
            return new JsonResponse(['error' => 'Cannot update the default theme, set default to false'], 400);
        }

        $this->themeManager->handleIsDefault($theme);

        $existingTheme->setName($theme->getName());
        $existingTheme->setColors($theme->getColors());
        $existingTheme->setIsDefault($theme->getIsDefault());

        $this->em->flush();

        return new JsonResponse(['message' => 'Theme updated successfully'], 200);
    }

    /**
     * @Route("/api/themes/{id}", methods={"DELETE"})
     */
    public function deleteTheme(int $id): JsonResponse
    {
        $theme = $this->em->getRepository(Theme::class)->find($id);

        if (!$theme) {
            return new JsonResponse(['error' => 'Theme not found'], 404);
        }

        if ($theme->getIsDefault()) {
            return new JsonResponse(['error' => 'Cannot delete the default theme'], 400);
        }

        $this->em->remove($theme);
        $this->em->flush();

        return new JsonResponse(['message' => 'Theme deleted successfully'], 200);
    }
}
