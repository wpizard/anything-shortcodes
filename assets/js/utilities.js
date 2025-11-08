window.anys = window.anys || {};

/**
 * Utilities Class.
 *
 * @since NEXT
 */
window.anys.utilities = class {

    /**
     * Serializes form fields into a nested object.
     *
     * Supports PHP-style names like:
     * - anys['post_title']
     * - anys['meta']['post_title']
     * - anys['tags'][]  (arrays supported)
     *
     * @since NEXT
     *
     * @param {HTMLFormElement} form
     *
     * @returns {Object}
     */
    static serializeForm( form ) {
        const formData = new FormData( form );
        const obj = {};

        for ( const [ key, value ] of formData.entries() ) {
            // Remove closing brackets and split by '['.
            const path = key
                .replace( /\]/g, '' )
                .split( '[' );

            let current = obj;

            path.forEach( ( part, index ) => {
                // If last part.
                if ( index === path.length - 1 ) {
                    // Handle array notation 'tags[]'.
                    if ( part === '' ) {
                        if ( ! Array.isArray( current ) ) {
                            current = [];
                        }

                        current.push( value );
                    } else if ( current[ part ] === undefined ) {
                        current[ part ] = value;
                    } else if ( Array.isArray( current[ part ] ) ) {
                        current[ part ].push( value );
                    } else {
                        current[ part ] = [ current[ part ], value ];
                    }
                } else {
                    if ( current[ part ] === undefined || typeof current[ part ] !== 'object' ) {
                        current[ part ] = {};
                    }

                    current = current[ part ];
                }
            } );
        }

        return obj;
    }

};
