<?php

declare(strict_types=1);

namespace App\FileStorage\Application;

final class SystemMediaCatalog
{
    /**
     * @return list<array{key: string, label: string, url: string}>
     */
    public function reviewAvatars(): array
    {
        return [
            ['key' => 'person-1', 'label' => 'Покупательница', 'url' => '/assets/system-media/people/person-1.jpg'],
            ['key' => 'person-2', 'label' => 'Покупатель', 'url' => '/assets/system-media/people/person-2.jpg'],
            ['key' => 'person-3', 'label' => 'Покупательница старшего возраста', 'url' => '/assets/system-media/people/person-3.jpg'],
        ];
    }

    /**
     * @return list<array{key: string, label: string, url: string}>
     */
    public function benefitIcons(): array
    {
        $labels = [
            'delivery' => 'Доставка',
            'quality' => 'Качество',
            'support' => 'Поддержка',
            'payment' => 'Удобная оплата',
            'return' => 'Возврат',
            'secure' => 'Безопасность',
            'gift' => 'Подарок',
            'stock' => 'В наличии',
            'pickup' => 'Самовывоз',
            'custom' => 'Индивидуальный подход',
        ];

        $icons = [];
        foreach ($labels as $key => $label) {
            $icons[] = [
                'key' => $key,
                'label' => $label,
                'url' => sprintf('/assets/system-media/benefits/%s.svg', $key),
            ];
        }

        return $icons;
    }

    /**
     * @return list<array{key: string, title: string, description: string, kind: string, items: list<array{key: string, label: string, url: string}>}>
     */
    public function groups(): array
    {
        return [
            [
                'key' => 'people',
                'title' => 'Фото для отзывов',
                'description' => 'Готовые портреты для карточек отзывов.',
                'kind' => 'avatar',
                'items' => $this->reviewAvatars(),
            ],
            [
                'key' => 'benefits',
                'title' => 'Иконки преимуществ',
                'description' => 'SVG-иконки для блока «Почему выбирают нас».',
                'kind' => 'icon',
                'items' => $this->benefitIcons(),
            ],
        ];
    }
}
