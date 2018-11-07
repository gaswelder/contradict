import "regenerator-runtime/runtime";
import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route } from "react-router-dom";
import Menu from "./src/menu";
import api from "./src/api";
import withData from "./src/with-data";
import TestPage from "./src/TestPage";

const MenuPage = withData(() => api.dicts())(Menu);

class App extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <div>
          <Route exact path="/" component={MenuPage} />
          <Route path="/:id/test" component={TestPage} />
        </div>
      </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById("app"));
