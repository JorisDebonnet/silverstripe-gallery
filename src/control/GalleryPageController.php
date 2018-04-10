<?php

namespace ilateral\SilverStripe\Gallery\Control;

use ilateral\SilverStripe\Gallery\Model\GalleryHub;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\PaginatedList;

class GalleryPageController extends GalleryHubController
{
    public function init() {
        parent::init();
    }

    public function PaginatedImages()
    {
        $list = $this->SortedImages();
        $limit = $this->ThumbnailsPerPage;

        $pages = PaginatedList::create($list, $this->getRequest());
        $pages->setpageLength($limit);

        return $pages;
    }

    protected function GalleryImage(Image $image)
    {
        return $this->ScaledImage($image);
    }

    protected function GalleryThumbnail(Image $image)
    {
        return $this->ScaledImage($image, true);
    }

    /**
     * Generate an image gallery from the Gallery template, if no images are
     * available, then return an empty string.
     *
     * @return string
     */
    public function Gallery()
    {
        if ($this->Images()->exists()) {

            // Create a list of images with generated gallery image and thumbnail
            $pages = $this->PaginatedImages();
            
            $vars = [
                'PaginatedImages' => $pages,
                'Width' => $this->ImageWidth,
                'Height' => $this->ImageHeight
            ];

            if (!GalleryHub::config()->get('scale_from_template')) {
                $images = ArrayList::create();
                foreach ($this->PaginatedImages() as $image) {
                    $image_data = $image->toMap();
                    $image_data["GalleryImage"] = $this->GalleryImage($image);
                    $image_data["GalleryThumbnail"] = $this->GalleryThumbnail($image);
                    $images->add(ArrayData::create($image_data));
                }
                $vars['Images'] = $images;
            }

            return $this->renderWith(
                [
                    'Gallery',
                    'ilateral\SilverStripe\Gallery\Includes\Gallery'
                ],
                $vars
            );
        } else {
            return "";
        }
    }
}
