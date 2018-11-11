import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route } from "react-router-dom";
import "regenerator-runtime/runtime";
import AddEntriesPage from "./src/AddEntriesPage";
import api from "./src/api";
import DictsList from "./src/DictsList";
import EntryPage from "./src/EntryPage";
import TestPage from "./src/TestPage";
import withData from "./src/with-data";

const MenuPage = withData(() => api.dicts())(DictsList);

class App extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <div>
          <a href="/">Home</a>
          <Route exact path="/" component={MenuPage} />
          <Route path="/:id/test" component={TestPage} />
          <Route path="/:id/add" component={AddEntriesPage} />
          <Route path="/entries/:id" component={EntryPage} />
        </div>
      </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById("app"));
