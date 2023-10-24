/**
 * This Error adds a property of errors,
 * so we can catch a types error and loop through all the errors from the server.
 *
 * @since 3.0.0
 */
class FormRequestError extends Error {
    public errors: Array<object>;

    constructor(errors: Array<object>, ...params) {
        // Pass remaining arguments (including vendor specific ones) to parent constructor
        super(...params);

        // Maintains proper stack trace for where our error was thrown (only available on V8)
        if (Error.captureStackTrace) {
            Error.captureStackTrace(this, FormRequestError);
        }

        this.name = 'FormRequestError';
        this.errors = errors;
    }
}

export default FormRequestError;
