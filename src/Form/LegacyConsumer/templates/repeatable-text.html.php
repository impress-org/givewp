<?php /** @var Give\Framework\FieldsAPI\Text $field */ ?>
<table id="<?= $field->getName() ?>" class="give-repeater-table" data-max-repeat="" data-field-type="repeat">
	<tbody>
		<tr>
			<td>
				<input
					type="text"
					name="<?= "{$field->getName()}[]" ?>"
					value="<?= $field->getDefaultValue() ?>"
					placeholder="<?= $field->getPlaceholder() ?>"
					<?= $field->isRequired() ? 'required' : '' ?>
					<?= $field->isReadOnly() ? 'readonly' : '' ?>
			   >
			</td>
			<td>
				<span
					class="ffm-clone-field give-tooltip hint--top"
					data-tooltip="Click here to add another field"
					aria-label="Click here to add another field"
				>
					<i class="give-icon give-icon-plus"></i>
				</span>
				<span
					class="ffm-clone-field give-tooltip hint--top"
					data-tooltip="Click here to remove this field"
					aria-label="Click here to remove this field"
				>
					<i class="give-icon give-icon-minus"></i>
				</span>
			</td>
		</tr>
	</tbody>
</table>
