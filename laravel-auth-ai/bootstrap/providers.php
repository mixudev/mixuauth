<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Dashboard\DashboardServiceProvider::class,
    App\Modules\Identity\IdentityServiceProvider::class,
    App\Modules\Authorization\AuthorizationServiceProvider::class,
    App\Modules\Communication\CommunicationServiceProvider::class,
    App\Modules\Security\SecurityServiceProvider::class,
    App\Modules\Authentication\AuthServiceProvider::class,
    App\Modules\Common\CommonServiceProvider::class,
    App\Modules\Timezone\TimezoneServiceProvider::class,
];
