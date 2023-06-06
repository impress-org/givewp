const debug = (props) => ('development' === process.env.NODE_ENV) ? console.log(props) : null;

export default debug;
