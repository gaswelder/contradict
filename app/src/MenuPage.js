import React from "react";
import api from "./api";
import DictsList from "./DictsList";

class MenuPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      result: null,
      error: null
    };
  }

  async componentDidMount() {
    try {
      const result = await api.dicts();
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
    if (this.state.result) return <DictsList data={this.state.result} />;
    return "Loading";
  }
}

export default MenuPage;
