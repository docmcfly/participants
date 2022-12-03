<?php
declare(strict_types = 1);

return [
    \Cylancer\Participants\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users'
    ],
    \Cylancer\Participants\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups'
    ]
];  
