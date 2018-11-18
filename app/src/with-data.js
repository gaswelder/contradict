import React from "react";

export default function withData(getData) {
  return function(Component) {
    return class withData extends React.Component {
      constructor(props) {
        super(props);
        this.state = {
          result: null,
          error: null
        };
      }

      async componentDidMount() {
        try {
          const result = await getData(this.props);
          this.setState({ result });
        } catch (error) {
          if (error.unauthorized) {
            history.pushState({}, "", "/login");
            return;
          }
          this.setState({ error });
        }
      }

      render() {
        if (this.state.error) return "Error";
        if (this.state.result) return <Component data={this.state.result} />;
        return "Loading";
      }
    };
  };
}
