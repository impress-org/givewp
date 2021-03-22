# Form Field Mediators

Form Field Mediators are intentionally separate from the Form Field with which they interact. The intent is to create a boudary between the structure of field data and how it relates to GiveWP using the WordPress hook system.

## Example

As an example, the [Required Field Mediator](RequiredField.php) integrates a collection of fields with the `give_donation_form_required_fields` filter hook to register error messages for each required field. In this case, the error message is a feature of the Form Field, but how that information is used is managed by the mediator. This boundary fulfills a seperation of concerns by de-coupling the data from its use.