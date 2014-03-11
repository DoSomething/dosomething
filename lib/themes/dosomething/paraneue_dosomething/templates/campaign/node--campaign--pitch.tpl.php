<section class="campaign--wrapper">

  <?php foreach ($hero_image as $key => $image_url ) :?>
    <header class="header <?php print $key . ' ' . $classes; ?>" <?php print (isset($image_url) ? 'style="background-image: url(' . $image_url . ');"' : ''); ?>>
      <div class="meta">
        <h1 class="title"><?php print $title; ?></h1>
        <p class="cta"><?php print $cta; ?></p>

        <?php if (isset($end_date)): ?><p class="date"><?php print $end_date; ?></p><?php endif; ?>

        <?php if (isset($signup_button)): ?><?php print render($signup_button); ?><?php endif; ?>

        <?php if (isset($scholarship)): ?>
        <?php //@TODO: Remove Trello-hosted placeholder ?>
        <img class="arrow" src="https://trello-attachments.s3.amazonaws.com/52de9089aa3032b85e9b0962/52e1724e23eeb26f4e9fc427/7e9e3ef8974d815230449b9829e98ac0/arrow.png" alt="Click the button!" />
        <p class="scholarship highlight-wrapper"><span class="highlight"><?php print $scholarship; ?></span></p>
        <?php endif; ?>

        <?php if (isset($sponsors)): ?>
        <div class="sponsor">
          <?php foreach ($sponsors as $key => $sponsor) :?>
            <?php print $sponsor['name']; ?>
            <?php if (isset($sponsor['img'])): print $sponsor['img']; endif; ?>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </header>
  <?php endforeach; ?>

  <footer class="boilerplate">
    <strong>A DoSomething.org Campaign</strong>
    <span>Join over 2.4 million young people taking action. Any Cause. Anytime. Anywhere.</span>
  </footer>
</section>
