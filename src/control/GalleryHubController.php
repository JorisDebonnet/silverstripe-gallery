<?php

namespace ilateral\SilverStripe\Gallery\Control;

use ilateral\SilverStripe\Gallery\Model\GalleryHub;
use PageController;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\PaginatedList;

class GalleryHubController extends PageController
{
    /**
     * Get a custom list of children with resized gallery images
     *
     * @return PaginatedList
     */
    public function PaginatedGalleries()
    {
        $children = $this->AllChildren();
        $limit = $this->ThumbnailsPerPage;
        $list = ArrayList::create();

        foreach ($children as $child) {
            $image = $child->SortedImages()->first();
            $child_data = $child->toMap();
            $child_data["Link"] = $child->Link();
            if ($image) {
                if (GalleryHub::config()->get('scale_from_template')) {
                    $child_data["Image"] = $image;
                } else {
                    $child_data["GalleryThumbnail"] = $this->ScaledImage($image, true);
                }
            } else {
                $child_data["GalleryThumbnail"] = null;
            }
            $list->add(ArrayData::create($child_data));
        }

        $pages = PaginatedList::create($list, $this->getRequest());
        $pages->setPageLength($limit);

        return $pages;
    }

    /**
     * Generate an image based on the provided type
     * (either )
     *
     * @param Image $image
     * @param string $thumbnail generate a smaller image (based on thumbnail settings)
     * @return void
     */
    protected function ScaledImage(Image $image, $thumbnail = false)
    {
        $img = false;
        $background = $this->PaddedImageBackground;
        
        if ($thumbnail == true) {
            $resize_type = $this->ThumbnailResizeType;
            $width = $this->ThumbnailWidth;
            $height = $this->ThumbnailHeight;
        } else {
            $resize_type = $this->ImageResizeType;
            $width = $this->ImageWidth;
            $height = $this->ImageHeight;
        }

        switch ($resize_type) {
            case 'crop':
                $img = $image->Fill($width,$height);
                break;
            case 'pad':
                $img = $image->Pad($width,$height,$background);
                break;
            case 'ratio':
                $img = $image->Fit($width,$height);
                break;
            case 'width':
                $img = $image->ScaleWidth($width);
                break;
            case 'height':
                $img = $image->ScaleHeight($height);
                break;
        }

        $this->extend("augmentImageResize", $image, $thumbnail, $img);

        return $img;
    }
}
