<?php

declare(strict_types=1);

namespace Stu\Module\Index;

use Stu\Control\ControllerTypeEnum;
use Stu\Control\GameController;
use Stu\Lib\SessionInterface;
use Stu\Module\Index\Action\CheckInput\CheckInput;
use Stu\Module\Index\Action\CheckInput\CheckInputRequest;
use Stu\Module\Index\Action\CheckInput\CheckInputRequestInterface;
use Stu\Module\Index\Action\Login\Login;
use Stu\Module\Index\Action\Login\LoginRequest;
use Stu\Module\Index\Action\Login\LoginRequestInterface;
use Stu\Module\Index\Action\Logout\Logout;
use Stu\Module\Index\Action\Register\Register;
use Stu\Module\Index\Action\Register\RegisterRequest;
use Stu\Module\Index\Action\Register\RegisterRequestInterface;
use Stu\Module\Index\Action\ResetPassword\ResetPassword;
use Stu\Module\Index\Action\ResetPassword\ResetPasswordRequest;
use Stu\Module\Index\Action\ResetPassword\ResetPasswordRequestInterface;
use Stu\Module\Index\Action\SendPassword\SendPassword;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequest;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequestInterface;
use Stu\Module\Index\View\Overview\Overview;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Module\Index\View\ShowHelp\ShowHelp;
use Stu\Module\Index\View\ShowImprint\ShowImprint;
use Stu\Module\Index\View\ShowLostPassword\ShowLostPassword;
use Stu\Module\Index\View\ShowRegistration\ShowRegistration;
use Stu\Module\Index\View\ShowResetPassword\ShowResetPassword;
use Stu\Module\Index\View\ShowResetPassword\ShowResetPasswordRequest;
use Stu\Module\Index\View\ShowResetPassword\ShowResetPasswordRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    CheckInputRequestInterface::class => autowire(CheckInputRequest::class),
    RegisterRequestInterface::class => autowire(RegisterRequest::class),
    LoginRequestInterface::class => autowire(LoginRequest::class),
    SendPasswordRequestInterface::class => autowire(SendPasswordRequest::class),
    ShowResetPasswordRequestInterface::class => autowire(ShowResetPasswordRequest::class),
    ResetPasswordRequestInterface::class => autowire(ResetPasswordRequest::class),
    ControllerTypeEnum::TYPE_INDEX => create(GameController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                CheckInput::ACTION_IDENTIFIER => autowire(CheckInput::class),
                Register::ACTION_IDENTIFIER => autowire(Register::class),
                Login::ACTION_IDENTIFIER => autowire(Login::class),
                SendPassword::ACTION_IDENTIFIER => autowire(SendPassword::class),
                ResetPassword::ACTION_IDENTIFIER => autowire(ResetPassword::class),
                Logout::ACTION_IDENTIFIER => autowire(Logout::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowHelp::VIEW_IDENTIFIER => autowire(ShowHelp::class),
                ShowImprint::VIEW_IDENTIFIER => autowire(ShowImprint::class),
                ShowRegistration::VIEW_IDENTIFIER => autowire(ShowRegistration::class),
                ShowFinishRegistration::VIEW_IDENTIFIER => autowire(ShowFinishRegistration::class),
                ShowLostPassword::VIEW_IDENTIFIER => autowire(ShowLostPassword::class),
                ShowResetPassword::VIEW_IDENTIFIER => autowire(ShowResetPassword::class),
            ]
        ),
];