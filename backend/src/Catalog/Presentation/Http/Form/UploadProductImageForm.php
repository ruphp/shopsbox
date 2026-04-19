<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Application\Dto\UploadProductImageInput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UploadProductImageForm
{
    public function fromRequest(Request $request, string $storeId, string $productId): UploadProductImageInput
    {
        $file = $request->files->get('image');
        if (!$file instanceof UploadedFile) {
            throw new BadRequestHttpException('Missing image file.');
        }

        if (!$file->isValid()) {
            throw new BadRequestHttpException('Uploaded image is not valid.');
        }

        $contents = file_get_contents($file->getPathname());
        if (!is_string($contents)) {
            throw new BadRequestHttpException('Unable to read uploaded image.');
        }

        return new UploadProductImageInput(
            $storeId,
            $productId,
            $file->getClientOriginalName(),
            (string) ($file->getMimeType() ?? $file->getClientMimeType()),
            (int) $file->getSize(),
            $contents,
        );
    }
}
