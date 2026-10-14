<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Application\UseCase;

use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaFileView;
use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Dto\UpdateStoreAppearanceInput;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\UseCase\UpdateStoreAppearanceUseCase;
use PHPUnit\Framework\TestCase;

final class UpdateStoreAppearanceUseCaseTest extends TestCase
{
    public function testItUpdatesOnlyAppearanceAndKeepsStoreData(): void
    {
        $current = $this->settings();
        $repository = $this->createMock(StoreRepository::class);
        $repository->expects(self::once())
            ->method('findSettingsByOwnerEmail')
            ->with('owner@example.test')
            ->willReturn($current);
        $repository->expects(self::once())
            ->method('updateSettingsForOwnerEmail')
            ->with(
                'owner@example.test',
                'Магазин',
                'Описание',
                'shop@example.test',
                '+79990000000',
                self::anything(),
                self::anything(),
                self::anything(),
                self::anything(),
                self::anything(),
                self::anything(),
                self::anything(),
                'RUB',
                'Asia/Yekaterinburg',
                self::callback(static fn (array $theme): bool => $theme === [
                    'configured' => true,
                    'template' => 'accent',
                    'primary_color' => '#16876b',
                    'accent' => 'green',
                    'hero_title' => 'Новая коллекция',
                    'hero_text' => 'Доставка по России',
                    'hero_eyebrow' => 'Осень 2026',
                    'hero_button_label' => 'Открыть каталог',
                    'hero_alignment' => 'center',
                    'hero_overlay' => 70,
                    'featured_title' => 'Выбор покупателей',
                    'featured_columns' => 5,
                    'featured_show_description' => false,
                    'seo_title' => 'Магазин новой коллекции',
                    'seo_description' => 'Новая коллекция с доставкой по России.',
                    'hero_slides' => [
                        [
                            'eyebrow' => 'Осень 2026',
                            'title' => 'Новая коллекция',
                            'text' => 'Доставка по России',
                            'button_label' => 'Открыть каталог',
                            'button_url' => '/products',
                            'secondary_label' => '',
                            'secondary_url' => '',
                            'alignment' => 'center',
                            'vertical_alignment' => 'bottom',
                            'content_width' => 'narrow',
                            'image_position' => 'right',
                            'overlay' => 70,
                            'image_id' => 'hero-id',
                            'image_url' => '/media/hero.jpg',
                        ],
                        [
                            'eyebrow' => '',
                            'title' => 'Второй слайд',
                            'text' => '',
                            'button_label' => '',
                            'button_url' => '',
                            'secondary_label' => 'Подробнее',
                            'secondary_url' => 'https://example.test/info',
                            'alignment' => 'right',
                            'vertical_alignment' => 'center',
                            'content_width' => 'medium',
                            'image_position' => 'center',
                            'overlay' => 40,
                            'image_id' => null,
                            'image_url' => null,
                        ],
                    ],
                    'hero_autoplay' => false,
                    'hero_interval' => 7,
                    'featured_layout' => 'carousel',
                    'featured_limit' => 10,
                    'featured_source' => 'category',
                    'featured_category_slug' => 'new-items',
                    'featured_product_ids' => [],
                    'category_images' => [
                        'new-items' => ['image_id' => 'category-id', 'image_url' => '/media/category.jpg'],
                    ],
                    'benefits_title' => 'Почему мы',
                    'benefits' => [[
                        'title' => 'Быстро',
                        'text' => 'Отправим заказ завтра.',
                        'icon' => 'quality',
                        'media_source' => 'system-benefit:quality',
                        'image_id' => null,
                        'image_url' => '/assets/system-media/benefits/quality.svg',
                        'color' => '#ff3344',
                    ]],
                    'content_title' => 'Наша мастерская',
                    'content_text' => 'Делаем товары сами.',
                    'content_image_id' => 'content-id',
                    'content_image_url' => '/media/content.jpg',
                    'content_image_position' => 'right',
                    'content_button_label' => 'Подробнее',
                    'content_button_url' => '/about',
                    'reviews_title' => 'Нас рекомендуют',
                    'reviews' => [[
                        'author' => 'Анна',
                        'text' => 'Всё понравилось.',
                        'rating' => 5,
                        'avatar' => 'person-4',
                        'media_source' => 'system-review:person-4',
                        'avatar_image_id' => null,
                        'avatar_url' => '/assets/system-media/people/person-4.jpg',
                        'avatar_shape' => 'rounded',
                    ]],
                    'faq_title' => 'Вопросы о заказе',
                    'faqs' => [['question' => 'Есть доставка?', 'answer' => 'Да, по России.']],
                    'sections' => ['hero', 'contacts', 'featured'],
                    'logo_image_id' => 'logo-id',
                    'logo_image_url' => '/media/logo.jpg',
                    'show_store_name_with_logo' => true,
                    'hero_image_id' => 'hero-id',
                    'hero_image_url' => '/media/hero.jpg',
                ]),
            )
            ->willReturn($current);

        $flusher = $this->createMock(EntityFlusher::class);
        $flusher->expects(self::once())->method('flush');
        $media = $this->createStub(StoreMediaLibraryRepository::class);
        $media->method('listByStore')->willReturn([
            $this->image('logo-id', '/media/logo.jpg'),
            $this->image('hero-id', '/media/hero.jpg'),
            $this->image('category-id', '/media/category.jpg'),
            $this->image('content-id', '/media/content.jpg'),
        ]);

        $result = (new UpdateStoreAppearanceUseCase($repository, $flusher, $media))->execute(
            new UpdateStoreAppearanceInput(
                ' OWNER@EXAMPLE.TEST ',
                'accent',
                '#16876B',
                ' Новая коллекция ',
                ' Доставка по России ',
                ['hero', 'contacts', 'featured', 'hero', 'unknown'],
                'logo-id',
                'hero-id',
                true,
                ' Осень 2026 ',
                ' Открыть каталог ',
                'center',
                70,
                ' Выбор покупателей ',
                5,
                false,
                ' Магазин новой коллекции ',
                ' Новая коллекция с доставкой по России. ',
                [
                    [
                        'eyebrow' => ' Осень 2026 ',
                        'title' => ' Новая коллекция ',
                        'text' => ' Доставка по России ',
                        'button_label' => ' Открыть каталог ',
                        'button_url' => ' /products ',
                        'image_id' => 'hero-id',
                        'alignment' => 'center',
                        'vertical_alignment' => 'bottom',
                        'content_width' => 'narrow',
                        'image_position' => 'right',
                        'overlay' => 70,
                    ],
                    [
                        'title' => ' Второй слайд ',
                        'secondary_label' => ' Подробнее ',
                        'secondary_url' => ' https://example.test/info ',
                        'alignment' => 'right',
                        'overlay' => 40,
                    ],
                ],
                false,
                7,
                'carousel',
                10,
                'category',
                ' new-items ',
                [],
                ['new-items' => 'category-id'],
                ' Почему мы ',
                [['title' => ' Быстро ', 'text' => ' Отправим заказ завтра. ', 'media' => 'system-benefit:quality', 'color' => '#FF3344']],
                ' Наша мастерская ',
                ' Делаем товары сами. ',
                'content-id',
                'right',
                ' Подробнее ',
                ' /about ',
                ' Нас рекомендуют ',
                [['author' => ' Анна ', 'text' => ' Всё понравилось. ', 'rating' => 5, 'media' => 'system-review:person-4', 'avatar_shape' => 'rounded']],
                ' Вопросы о заказе ',
                [['question' => ' Есть доставка? ', 'answer' => ' Да, по России. ']],
            ),
        );

        self::assertSame($current, $result);
    }

    public function testItRejectsUnknownTemplate(): void
    {
        $repository = $this->createStub(StoreRepository::class);
        $repository->method('findSettingsByOwnerEmail')->willReturn($this->settings());
        $flusher = $this->createStub(EntityFlusher::class);
        $media = $this->createStub(StoreMediaLibraryRepository::class);

        $this->expectException(InvalidTenantInput::class);

        (new UpdateStoreAppearanceUseCase($repository, $flusher, $media))->execute(
            new UpdateStoreAppearanceInput('owner@example.test', 'unknown', '#0077b6', '', '', ['hero']),
        );
    }

    public function testItKeepsPublishedAppearanceWhenSavingDraft(): void
    {
        $publishedTheme = [
            'configured' => true,
            'template' => 'classic',
            'primary_color' => '#0077b6',
            'hero_title' => 'Опубликованный заголовок',
        ];
        $current = $this->settings($publishedTheme);
        $repository = $this->createMock(StoreRepository::class);
        $repository->method('findSettingsByOwnerEmail')->willReturn($current);
        $repository->expects(self::once())
            ->method('updateSettingsForOwnerEmail')
            ->willReturnCallback(function (...$arguments) use ($publishedTheme, $current): StoreSettingsView {
                $savedTheme = $arguments[14];
                self::assertSame($publishedTheme, array_diff_key($savedTheme, ['appearance_draft' => true]));
                self::assertSame('accent', $savedTheme['appearance_draft']['template']);
                self::assertSame('Черновик', $savedTheme['appearance_draft']['hero_title']);

                return $current;
            });
        $flusher = $this->createMock(EntityFlusher::class);
        $flusher->expects(self::once())->method('flush');
        $media = $this->createStub(StoreMediaLibraryRepository::class);
        $media->method('listByStore')->willReturn([]);

        (new UpdateStoreAppearanceUseCase($repository, $flusher, $media))->execute(
            new UpdateStoreAppearanceInput(
                ownerEmail: 'owner@example.test',
                template: 'accent',
                primaryColor: '#ff5a3c',
                heroTitle: 'Черновик',
                heroText: '',
                sections: ['hero'],
                action: 'draft',
            ),
        );
    }

    public function testItRejectsImageFromAnotherStore(): void
    {
        $repository = $this->createStub(StoreRepository::class);
        $repository->method('findSettingsByOwnerEmail')->willReturn($this->settings());
        $flusher = $this->createStub(EntityFlusher::class);
        $media = $this->createStub(StoreMediaLibraryRepository::class);
        $media->method('listByStore')->willReturn([$this->image('available-id', '/media/available.jpg')]);

        $this->expectException(InvalidTenantInput::class);

        (new UpdateStoreAppearanceUseCase($repository, $flusher, $media))->execute(
            new UpdateStoreAppearanceInput(
                'owner@example.test',
                'classic',
                '#0077b6',
                '',
                '',
                ['hero'],
                'foreign-id',
            ),
        );
    }

    /** @param array<string, mixed> $themeSettings */
    private function settings(array $themeSettings = []): StoreSettingsView
    {
        return new StoreSettingsView(
            tenantId: 'tenant-id',
            storeId: 'store-id',
            name: 'Магазин',
            slug: 'shop',
            domain: 'shop.test',
            status: 'active',
            defaultCurrency: 'RUB',
            timezone: 'Asia/Yekaterinburg',
            publicDescription: 'Описание',
            contactEmail: 'shop@example.test',
            contactPhone: '+79990000000',
            themeSettings: $themeSettings,
        );
    }

    private function image(string $id, string $url): StoreMediaFileView
    {
        return new StoreMediaFileView(
            id: $id,
            key: 'media/'.$id.'.jpg',
            publicUrl: $url,
            mimeType: 'image/jpeg',
            mediaType: 'image',
            size: 1024,
            usage: 'Товар',
            createdAt: '2026-10-12 12:00',
        );
    }
}
