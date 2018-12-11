import React from "react";
import withAPI from "./components/withAPI";
import Dictionary from "./components/Dictionary";

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
    if (!this.state.result) {
      return "Loading";
    }
    const dicts = this.state.result.dicts;
    return dicts.map(d => <Dictionary key={d.id} dict={d} />);
  }
}

export default withAPI(MenuPage);
