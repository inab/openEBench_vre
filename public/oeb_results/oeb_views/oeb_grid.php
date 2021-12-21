<div class="portfolio-content portfolio-3">

    <input type="hidden" id="base-url" value="<?php echo $GLOBALS['BASEURL']; ?>" />

    <?php

    $kw = array();
    foreach ($toolList as $t) {
        foreach ($t['keywords'] as $tk) {
            if ($tk == "next gen seq") $tk = "next_gen_seq";
            $kw[] = $tk;
        }
    }

    $kw = array_unique($kw);
    sort($kw);

    ?>

    <div class="clearfix">
        <div id="js-filters-lightbox-gallery2" class="cbp-l-filters-button cbp-l-filters-left">
            <div data-filter="*" class="cbp-filter-item-active cbp-filter-item btn blue btn-outline uppercase">All</div>

            <?php foreach ($kw as $k) { ?>
                <div data-filter=".<?php echo $k; ?>" class="cbp-filter-item btn blue btn-outline uppercase"><?php echo str_replace("_", " ", $k);
                                                                                                                $k; ?></div>
            <?php } ?>

        </div>
    </div>
    <div id="js-grid-lightbox-gallery" class="cbp">

        <?php

        foreach ($toolList as $t) {

            $kw = implode(" ", $t['keywords']);

            if (strpos($kw, 'visualization') === false) $type = 'tools';
            else $type = 'visualizers';

            $kw = str_replace("next gen seq", "next_gen_seq", $kw);


        ?>

            <div class="cbp-item <?php echo $kw; ?>">
                <!-- REMOVE cbp-singlePageInline to go to new page -->
                <a href="<?php echo $type; ?>/<?php echo $t['_id']; ?>/assets/home/index.html" class="cbp-caption cbp-singlePageInline" data-title="<?php echo $t['title']; ?>" rel="nofollow">
                    <div class="cbp-caption-defaultWrap">
                        <img src="<?php echo $type; ?>/<?php echo $t['_id']; ?>/assets/home/logo.png" alt="">
                    </div>
                    <div class="cbp-caption-activeWrap">
                        <div class="cbp-l-caption-alignLeft">
                            <div class="cbp-l-caption-body">
                                <div class="cbp-l-caption-title"><?php echo $t['title']; ?></div>
                                <div class="cbp-l-caption-desc"><?php echo $t['short_description']; ?></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        <?php } ?>

    </div>

</div>