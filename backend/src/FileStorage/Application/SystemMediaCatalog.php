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
            ['key' => 'person-1', 'label' => 'Женщина, 30 лет', 'url' => '/assets/system-media/people/person-1.jpg'],
            ['key' => 'person-2', 'label' => 'Мужчина, 35 лет', 'url' => '/assets/system-media/people/person-2.jpg'],
            ['key' => 'person-3', 'label' => 'Женщина, 55 лет', 'url' => '/assets/system-media/people/person-3.jpg'],
            ['key' => 'person-4', 'label' => 'Женщина, 28 лет', 'url' => '/assets/system-media/people/person-4.jpg'],
            ['key' => 'person-5', 'label' => 'Мужчина, 45 лет', 'url' => '/assets/system-media/people/person-5.jpg'],
            ['key' => 'person-6', 'label' => 'Женщина, 38 лет', 'url' => '/assets/system-media/people/person-6.jpg'],
            ['key' => 'person-7', 'label' => 'Мужчина, 25 лет', 'url' => '/assets/system-media/people/person-7.jpg'],
            ['key' => 'person-8', 'label' => 'Женщина, 60 лет', 'url' => '/assets/system-media/people/person-8.jpg'],
            ['key' => 'person-9', 'label' => 'Мужчина, 60 лет', 'url' => '/assets/system-media/people/person-9.jpg'],
            ['key' => 'person-10', 'label' => 'Женщина, 45 лет', 'url' => '/assets/system-media/people/person-10.jpg'],
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
