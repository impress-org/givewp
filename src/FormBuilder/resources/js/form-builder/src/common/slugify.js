 export default function slugify(value){
        return value
            .toLowerCase()
            .replace(/\s|_/g, '-') // Replace spaces and underscores with dashes
            .replace(/[^a-zA-Z\d\s-]/g, '') // Replace non-alphanumeric characters (other than dashes)
            .replace(/-$/g, ''); // Remove trailing dash
    };
