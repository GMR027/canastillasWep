<?php /** @var \Model\Compra $compra */ ?>
<span
  class="insignia insignia--<?= s($compra->pagoEstadoClave()) ?>"
  title="Pagado <?= s(dinero($compra->cobrado)) ?> de <?= s(dinero($compra->total)) ?> · Saldo pendiente <?= s(dinero($compra->saldoPendiente())) ?>"
><?= s($compra->pagoEtiqueta()) ?></span>
