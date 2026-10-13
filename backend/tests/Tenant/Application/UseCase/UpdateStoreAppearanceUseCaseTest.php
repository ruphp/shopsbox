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

    private function settings(): StoreSettingsView
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
