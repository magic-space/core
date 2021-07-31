<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class StationUtility implements StationUtilityInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ConstructionProgressRepositoryInterface $constructionProgressRepository;

    private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository;

    private ShipCreatorInterface $shipCreator;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ConstructionProgressRepositoryInterface $constructionProgressRepository,
        ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        ShipCreatorInterface $shipCreator,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->constructionProgressRepository = $constructionProgressRepository;
        $this->constructionProgressModuleRepository = $constructionProgressModuleRepository;
        $this->shipCreator = $shipCreator;
        $this->loggerUtil = $loggerUtil;
        $this->loggerUtil->init();
    }
    public static function canShipBuildConstruction(ShipInterface $ship): bool
    {
        if (!$ship->isShuttleRampHealthy()) {
            return false;
        }

        // check if ship has the required workbee amount
        $workbeeCount = 0;
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isWorkbee()) {
                $workbeeCount += $stor->getAmount();
            }
        }
        if ($workbeeCount < BuildConstruction::NEEDED_WORKBEES) {
            return false;
        }

        // check if ship has the needed resources
        foreach (BuildConstruction::NEEDED_RESOURCES as $key => $amount) {
            if (
                !$ship->getStorage()->containsKey($key)
                || $ship->getStorage()->get($key)->getAmount() < $amount
            ) {
                return false;
            }
        }

        return true;
    }

    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->shipBuildplanRepository->getStationBuildplansByUser($userId);
    }

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?ShipBuildplanInterface
    {
        $this->loggerUtil->log(sprintf('getBuidplanIfResearchedByUser. planId: %d, userId: %d', $planId, $userId));

        $plans = $this->getStationBuildplansByUser($userId);

        foreach ($plans as $plan) {
            $this->loggerUtil->log(sprintf('planId: %d', $plan->getId()));

            if ($plan->getId() === $planId) {
                return $plan;
            }
        }

        return null;
    }

    public function getDockedWorkbeeCount(ShipInterface $ship): int
    {
        $dockedWorkbees = 0;
        foreach ($ship->getDockedShips() as $docked) {
            $commodity = $docked->getRump()->getCommodity();
            if ($commodity !== null && $commodity->isWorkbee()) {
                $dockedWorkbees += 1;
            }
        }

        return $dockedWorkbees;
    }

    public function hasEnoughDockedWorkbees(ShipInterface $ship, ShipRumpInterface $rump): bool
    {
        return $this->getDockedWorkbeeCount($ship) >= $rump->getNeededWorkbees();
    }

    public function getConstructionProgress(ShipInterface $ship): ?ConstructionProgressInterface
    {
        return $this->constructionProgressRepository->getByShip($ship->getId());
    }

    public function reduceRemainingTicks(ConstructionProgressInterface $progress): void
    {
        $progress->setRemainingTicks($progress->getRemainingTicks() - 1);
        $this->constructionProgressRepository->save($progress);
    }

    public function finishStation(ShipInterface $ship, ConstructionProgressInterface $progress): void
    {
        $plan = $ship->getBuildplan();
        $rump = $ship->getRump();

        // transform ship
        $this->shipCreator->createBy($ship->getUser()->getId(), $rump->getId(), $plan->getId(), null, $progress);

        // set progress finished
        $progress->setRemainingTicks(0);
        $this->constructionProgressRepository->save($progress);
    }
}