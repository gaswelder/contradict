import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import "regenerator-runtime/runtime";
import AddEntriesPage from "./src/AddEntriesPage";
import api from "./src/api";
import EntryPage from "./src/EntryPage";
import TestPage from "./src/TestPage";
import LoginPage from "./src/LoginPage";
import MenuPage from "./src/MenuPage";

async function logout() {
  await api.logout();
  history.pushState({}, "", "/login");
}

class App extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <div>
          <a href="/">Home</a>
          <button onClick={logout}>Logout</button>
          <Switch>
            <Route exact path="/" component={MenuPage} />
            <Route path="/:id/test" component={TestPage} />
            <Route path="/:id/add" component={AddEntriesPage} />
            <Route path="/entries/:id" component={EntryPage} />
            <Route path="/login" component={LoginPage} />
            <Route component={() => "Not Found"} />
          </Switch>
        </div>
      </BrowserRouter>
    );
  }
}

ReactDOM.render(<App />, document.getElementById("app"));
