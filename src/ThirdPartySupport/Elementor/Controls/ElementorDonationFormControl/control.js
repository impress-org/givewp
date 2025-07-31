window.addEventListener('elementor/init', () => {
   const givewpDonationFormControl = elementor.modules.controls.BaseData.extend({
       onReady: function () {
           console.log('onReady');
           this.controlSelect = this.$el.find('data-setting[name="form_id"]');
           this.savedValue = this.controlSelect.val();
           console.log(this.controlSelect);

           this.controlSelect.on('change', (e) => {
               console.log('change', e);
               this.saveValue();
           });
       },

       saveValue: function () {
           let val = this.controlSelect.val();
           this.setValue(val);
       },

       onBeforeDestroy: function () {
           this.saveValue();
       },
   });

	elementor.addControlView( 'givewp_donation_form_control', givewpDonationFormControl );
});
