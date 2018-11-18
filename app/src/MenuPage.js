import React from "react";
import DictsList from "./DictsList";
import withAPI from "./withAPI";
class MenuPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      result: null
    };
  }

  async componentDidMount() {
    const result = await this.props.api.dicts();
    this.setState({ result });
  }

  render() {
    if (this.state.result) return <DictsList data={this.state.result} />;
    return "Loading";
  }
}

export default withAPI(MenuPage);
