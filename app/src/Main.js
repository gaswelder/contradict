import React from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import AddEntriesPage from "./AddEntriesPage";
import api from "./api";
import EntryPage from "./EntryPage";
import TestPage from "./TestPage";
import LoginPage from "./LoginPage";
import MenuPage from "./MenuPage";

async function logout() {
  await api.logout();
  history.pushState({}, "", "/login");
}

class Main extends React.Component {
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

export default Main;
