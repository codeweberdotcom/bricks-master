<?php

function new_callback()
{
?>

   <div class="wrap">
      <h1>Create a New Child Theme</h1>
      <form id="create-child-theme-form">
         <label for="child-theme-name">Child Theme Name:</label>
         <input type="text" id="child-theme-name" name="child-theme-name" required />
         <button type="button" id="create-child-theme-button" class="button-primary">Create Child Theme</button>
      </form>
      <div id="response-message" style="margin-top: 20px;"></div>
   </div>
<?php



}
