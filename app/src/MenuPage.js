import React from "react";
import withAPI from "./components/withAPI";
import Dictionary from "./components/Dictionary";
import Resource from "./components/Resource";

class MenuPage extends React.Component {
  render() {
    return (
      <Resource getPromise={this.props.api.dicts}>
        {data => data.dicts.map(d => <Dictionary key={d.id} dict={d} />)}
      </Resource>
    );
  }
}

export default withAPI(MenuPage);
