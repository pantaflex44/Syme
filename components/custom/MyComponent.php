<?php

declare(strict_types=1);

namespace components\custom {

    use components\core\Data;
    use components\core\Route;

    Route::extendWith(MyComponent::class);

    class MyComponent
    {

        public function __construct(Data $data, array $attributes)
        {
            $data->set('my component', $attributes);
        }

    }

}