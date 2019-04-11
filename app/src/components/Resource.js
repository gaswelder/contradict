import React from "react";

class Resource extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      result: null
    };
  }

  async componentDidMount() {
    const result = await this.props.getPromise();
    this.setState({ result, loading: false });
  }

  render() {
    const { loading, result, error } = this.state;
    const { children } = this.props;

    if (loading) {
      return "Loading";
    }

    if (error) {
      return "Error: " + error.toString();
    }

    return children(result);
  }
}

export default Resource;
