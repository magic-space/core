<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LandShuttle;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LandShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LAND_SHUTTLE';

    private ShipRepositoryInterface $shipRepository;

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private EntityManagerInterface $entityManager;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ShipRemoverInterface $shipRemover;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipLoaderInterface $shipLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipCrewRepositoryInterface $shipCrewRepository,
        EntityManagerInterface $entityManager,
        TroopTransferUtilityInterface $troopTransferUtility,
        ShipRemoverInterface $shipRemover,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipLoader = $shipLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->entityManager = $entityManager;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->shipRemover = $shipRemover;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('shuttle'),
            $userId
        );

        $target = $this->shipRepository->find(request::getIntFatal('id'));
        if ($target === null) {
            return;
        }
        if (!$this->positionChecker->checkPosition($ship, $target)) {
            return;
        }
        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        if ($target->getWarpState()) {
            $game->addInformation(_("Das Zielschiff hat den Warpantrieb aktiviert"));
            return;
        }

        if ($target->getShieldState()) {
            $game->addInformation(_("Das Zielschiff hat die Schilde aktiviert"));
            return;
        }

        // check if target got shuttle ramp
        if (!$target->hasShuttleRamp()) {
            $game->addInformation(_("Das Zielschiff verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if target shuttle ramp is healthy
        if (!$target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe vom Zielschiff ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$target->hasFreeShuttleSpace(null)) {
            $game->addInformation(_("Die Shuttle-Rampe des Zielschiffs ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($target) < $ship->getCrewCount()) {
            $game->addInformation(_('Das Zielschiff verfügt nicht über genügend Crew-Quartiere'));
            return;
        }

        // send shuttle to target storage
        $this->shipStorageManager->upperStorage(
            $target,
            $ship->getRump()->getCommodity(),
            1
        );

        // land workbee and transfer crew
        $this->landWorkbee($ship, $target);

        $game->addInformation("Shuttle erfolgreich gelandet");
    }

    private function landWorkbee(ShipInterface $ship, ShipInterface $target): void
    {
        foreach ($ship->getCrewlist() as $shipCrew) {
            $shipCrew->setShip($target);
            $target->getCrewlist()->add($shipCrew);
            $this->shipCrewRepository->save($shipCrew);
        }
        $ship->getCrewlist()->clear();
        $this->entityManager->flush();

        $this->shipRemover->remove($ship);

        $this->shipRepository->save($target);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}