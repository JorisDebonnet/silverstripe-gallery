<?php

namespace ilateral\SilverStripe\Gallery\Model;

use Page;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use ilateral\SilverStripe\Gallery\Control\GalleryHubController;

/**
 * Generate a page that can display it's children as a grid of thumbnails
 * 
 * @package gallery
 */
class GalleryHub extends Page
{
    /**
     * Option to scale images from template files instead of from the Page's settings.
     *
     * If this is set to true, then
     * - For GallerHubs: use $Image instead of $GalleryThumbnail in GalleryHub.ss
     * - For GalleryPages: change Gallery.ss to contain something like this:
     *   <% loop $PaginatedImages %>
     *     <img src="$Fill(150,150).URL" alt="$Title.ATT" data-url="$Fit(950,500).URL">
     *   <% end_loop %>
     *
     * @config
     *
     * @var boolean
     */
    private static $scale_from_template = false;

    /**
     * @var string
     */
    private static $description = 'Display child galleries as a thumbnail grid';

    private static $icon = "resources/i-lateral/silverstripe-gallery/client/dist/images/gallery-hub.png";

    private static $table_name = "GalleryHub";

    /**
     * @var array
     */
    private static $allowed_children = [
        GalleryPage::class,
    ];

    private static $db = [
        "ShowSideBar" => "Boolean",
        "ShowImageTitles" => "Boolean",
        "ThumbnailWidth" => "Int",
        "ThumbnailHeight" => "Int",
        "ThumbnailResizeType" => "Enum(array('crop','pad','ratio','width','height'), 'crop')",
        "PaddedImageBackground" => "Varchar",
        "ThumbnailsPerPage" => "Int"
    ];

    private static $defaults = [
        "ThumbnailWidth" => 150,
        "ThumbnailHeight" => 150,
        "ThumbnailsPerPage" => 18,
        "ThumbnailResizeType" => 'crop',
        "PaddedImageBackground" => "ffffff"
    ];

    public function getControllerName() {
        return GalleryHubController::class;
    }

    public function getSettingsFields()
    {
        $fields = parent::getSettingsFields();

        $settings = [
            CheckboxField::create('ShowSideBar'),
            CheckboxField::create('ShowImageTitles'),
            NumericField::create('ThumbnailsPerPage'),
        ];
        if (!self::config()->get('scale_from_template')) {
            $settings = array_merge(
                $settings,
                [
                    NumericField::create("ThumbnailWidth"),
                    NumericField::create("ThumbnailHeight"),
                    DropdownField::create("ThumbnailResizeType")
                        ->setSource($this->dbObject("ThumbnailResizeType")->enumValues()),
                    TextField::create("PaddedImageBackground")
                ]
            );
        }

        $fields->addFieldsToTab(
            "Root.Settings",
            $settings
        );

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // default settings (if not set)
        $defaults = $this->config()->defaults;
        $this->ThumbnailWidth = ($this->ThumbnailWidth) ? $this->ThumbnailWidth : $defaults["ThumbnailWidth"];
        $this->ThumbnailHeight = ($this->ThumbnailHeight) ? $this->ThumbnailHeight : $defaults["ThumbnailHeight"];
        $this->ThumbnailsPerPage = ($this->ThumbnailsPerPage) ? $this->ThumbnailsPerPage : $defaults["ThumbnailsPerPage"];
        $this->PaddedImageBackground = ($this->PaddedImageBackground) ? $this->PaddedImageBackground : $defaults["PaddedImageBackground"];
    }
}
