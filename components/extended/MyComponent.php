<?php

declare(strict_types=1);

namespace components\extended {

    use components\core\Data;
    use components\core\Route;

    class MyComponent {

        /** Se produit lorsque le composant est chargé
         * @return void
         */
        public static function __required(): void {
            Route::extendWith(MyComponent::class);
        }

        public function __construct(Data $data, array $attributes) {
            $data->set('my component', $attributes);
        }
    }

}