document.addEventListener("DOMContentLoaded", function() {
  // Attach click listener to all buttons with the 'iconpicker' class
  document.querySelectorAll(".iconpicker").forEach(function(button) {
    button.addEventListener("click", function() {
      // Find associated input
      var inputFieldId = this.getAttribute("data-input-id");
      var inputField = this.closest(".carbon-field").querySelector('input[data-name="' + inputFieldId + '"]');

      // If a picker instance already exists, destroy it first
      if (this._iconPickerInstance) {
        this._iconPickerInstance.destroy();
      }

      // Initialize new IconPicker instance
      this._iconPickerInstance = new IconPicker(this, {
        icons: [
          'fas fa-car',
          'fas fa-cogs',
          'fas fa-phone',
          'fas fa-wrench',
          'fas fa-truck',
          'fas fa-tools'
        ],
        theme: 'bootstrap',
        searchable: true,
        showSelectedIn: inputField,
        searchPlaceholder: 'Search icons…',
        closeOnSelect: true
      });

      // Show picker manually
      this._iconPickerInstance.show();
    });
  });
});
