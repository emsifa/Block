<div>

  <?= $this->component('components.alert', ['class' => 'alert-info']) ?>
    <?= $this->slot('title') ?>
    <span>Alert Title</span>
    <?= $this->endslot() ?>
    <p>Lorem ipsum dolor sit amet</p>

    <?= $this->component('components.whatever') ?>
      <?= $this->slot('var') ?>
      <strong>Hola</strong>
      <?= $this->endslot() ?>

      <div>
        Foobar
      </div>

    <?= $this->endcomponent() ?>

  <?= $this->endcomponent() ?>

</div>