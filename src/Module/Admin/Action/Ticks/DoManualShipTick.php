<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DoManualShipTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHIP_TICK';

    private ShipTickManagerInterface $shipTickManager;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipTickManagerInterface $shipTickManager,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->shipTickManager = $shipTickManager;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //check if single or all ships
        if (!request::getVarByMethod(request::postvars(), 'shiptickid')) {
            $this->shipTickManager->work();
            $game->addInformation("Der Schiff-Tick für alle Schiffe wurde durchgeführt!");
        } else {
            $shipId = request::postInt('shiptickid');
            $ship = $this->shipRepository->find($shipId);

            $this->shipTick->work($ship);
            $this->entityManager->flush();

            $game->addInformation("Der Schiff-Tick für dieses Schiff wurde durchgeführt!");
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
