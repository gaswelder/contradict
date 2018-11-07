import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route } from "react-router-dom";
import "regenerator-runtime/runtime";
import api from "./src/api";
import EntryPage from "./src/EntryPage";
import Menu from "./src/menu";
import TestPage from "./src/TestPage";
import withData from "./src/with-data";

const MenuPage = withData(() => api.dicts())(Menu);

class App extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <div>
          <Route exact path="/" component={MenuPage} />
          <Route path="/:id/test" component={TestPage} />
          <Route path="/entries/:id" component={EntryPage} />
        </div>
      </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById("app"));
