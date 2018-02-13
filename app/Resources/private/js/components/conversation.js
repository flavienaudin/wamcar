/* ===========================================================================
   Conversation
   =========================================================================== */

let $removeButton = document.getElementById('js-remove-vehicle-selected');

if ($removeButton) {
  let $input = document.getElementById('message_vehicle');

  $removeButton.addEventListener('click', () => {
    $input.value = '';
    const preview = $removeButton.closest('.messages-item-vehicle-taken');
    preview.remove();
  });
}
