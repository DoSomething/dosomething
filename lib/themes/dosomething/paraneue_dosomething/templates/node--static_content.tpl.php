<section class="static_content-wrapper">
  <article id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

    <div class="header-wrapper">
      <header class="header">
        <h1 class="title"><?php print $title; ?></h1>
        <?php if (isset($subtitle)): ?>
	  <h3 class="subtitle"><?php print $subtitle; ?></h3>
        <?php endif; ?>
      </header>
    </div>

    <div class="intro-wrapper">
      <div class="intro">
        <?php if (isset($intro_title)): ?>
          <h2><?php print $intro_title; ?></h2>
        <?php endif; ?>
        <?php if (isset($intro)): ?>
          <div class="intro-content"><?php print $intro; ?></div>
        <?php endif; ?>
        <?php if (isset($intro_image)): ?>
          <?php print $intro_image; ?>
        <?php elseif (isset($intro_video)): ?>
          <?php print $intro_video; ?>
        <?php endif; ?>
      </div>
    </div>
    
    <?php if (isset($call_to_action)): ?>
      <div class="cta-wrapper">
        <div class="cta">
	  <h3><?php print $call_to_action; ?></h3>
          <div class="cta_button"><?php print $cta_link; ?></div>
        </div>
      </div>
    <?php endif; ?>
  
    <?php if (isset($galleries)): ?>
      <div class="gallery-wrapper">
        <div class="gallery">
          <?php foreach ($galleries as $gallery): ?>
	  <div class="gallery-item">
	    <?php if (isset($gallery['image'])): ?>
	      <?php print $gallery['image']; ?>
	    <?php endif; ?>
	    <?php if (isset($gallery['image_title'])): ?>
              <h3><?php print $gallery['image_title']; ?></h3>
            <?php endif; ?>
	    <?php if (isset($gallery['image_description'])): ?>
	      <div class="gallery-description"><?php print $gallery['image_description']; ?></div>
            <?php endif; ?>
	  </div>
          <?php endforeach; ?>
        </div>
      </div>
    <? endif; ?>

    <?php if (isset($additional_text)): ?>
    <div class="additional-text-wrapper">
      <div class="additional-text">
        <?php if (isset($additional_text_title)): ?>
          <h2><?php print $additional_text_title; ?></h2>
        <?php endif; ?>
        
        <p><?php print $additional_text; ?></p>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($call_to_action)): ?>
      <div class="cta-wrapper">
        <div class="cta">
	  <h3><?php print $call_to_action; ?></h3>
          <div class="cta_button"><?php print $cta_link; ?></div>
        </div>
      </div>
    <?php endif; ?>
  </article>
</section>