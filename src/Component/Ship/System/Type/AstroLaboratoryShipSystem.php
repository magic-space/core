<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Orm\Entity\ShipInterface;

final class AstroLaboratoryShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const FINALIZING_ENERGY_COST = 15;

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryLib = $astroEntryLib;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiv ist');
            return false;
        }

        if (!$ship->getLss()) {
            $reason = _('die Langstreckensensoren nicht aktiv sind');
            return false;
        }

        if (!$ship->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        return true;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }
    }
}
