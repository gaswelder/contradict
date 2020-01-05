import React from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import AddEntriesPage from "./AddEntriesPage";
import EntryPage from "./EntryPage";
import Header from "./Header";
import LoginPage from "./LoginPage";
import MenuPage from "./MenuPage";
import TestPage from "./TestPage";
import Export from "./Export";
import DictPage from "./DictPage";

function page(Component, header = true) {
  return function page(props) {
    return (
      <main className="page">
        {header && <Header />}
        <Component {...props} />
      </main>
    );
  };
}

class Main extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <Switch>
          <Route exact path="/" component={page(MenuPage)} />
          <Route path="/:id/test" component={page(TestPage)} />
          <Route path="/:id/add" component={page(AddEntriesPage)} />
          <Route path="/dicts/:id" component={page(DictPage)} />
          <Route path="/entries/:id" component={page(EntryPage)} />
          <Route path="/login" component={page(LoginPage, false)} />
          <Route path="/export" component={page(Export)} />
          <Route component={page(() => "Not Found")} />
        </Switch>
      </BrowserRouter>
    );
  }
}

export default Main;
