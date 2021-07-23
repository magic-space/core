<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShuttleManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHUTTLE_MANAGEMENT';

    private ShowShuttleManagementRequestInterface $request;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ShowShuttleManagementRequestInterface $request,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->request = $request;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->request->getShipId());
        $colony = $this->colonyRepository->find($this->request->getColonyId());

        if ($game->getUser() !== $colony->getUser()) {
            return;
        }

        if ($game->getUser() !== $ship->getUser()) {
            $game->addInformation("Diese Funktion ist derzeit nur bei eigenen Schiffen möglich");
            return;
        }

        $game->setPageTitle("Shuttle Management");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/shuttlemanagement');

        $shuttles = [];

        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $smi = new ShuttleManagementItem($stor->getCommodity());
                $smi->setCurrentLoad($stor->getAmount());

                $shuttles[$stor->getCommodity()->getId()] = $smi;
            }
        }

        foreach ($colony->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                if (in_array($stor->getCommodity()->getId(), $shuttles)) {
                    $smi = $shuttles[$stor->getCommodity()->getId()];
                    $smi->setColonyLoad($stor->getAmount());
                } else {
                    $smi = new ShuttleManagementItem($stor->getCommodity());
                    $smi->setColonyLoad($stor->getAmount());

                    $shuttles[$stor->getCommodity()->getId()] = $smi;
                }
            }
        }


        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('AVAILABLE_SHUTTLES', $shuttles); // array aller Shuttle-Typen, mit currentAnzahl und maxAnzahl
        $game->setTemplateVar('ERROR', false);
    }
}
